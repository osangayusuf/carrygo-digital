<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaystackTransaction;
use App\Services\PaystackService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaystackTransactionController extends Controller
{
    public function __construct(
        private readonly PaystackService $paystackService,
        private readonly WalletService $walletService,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->filled('search')
            ? trim($request->string('search')->toString())
            : null;

        $statusFilter = $request->filled('status')
            ? $request->string('status')->toString()
            : null;

        $query = PaystackTransaction::query()
            ->with('user')
            ->orderBy('id', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })->orWhere('reference', 'like', "%{$search}%");
            });
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $transactions = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/PaystackTransactions/Index', [
            'transactions' => $transactions,
            'filters' => [
                'search' => $search,
                'status' => $statusFilter,
            ],
            'availableStatuses' => ['pending', 'success', 'failed'],
        ]);
    }

    public function requery(PaystackTransaction $transaction): RedirectResponse
    {
        $paystackData = $this->paystackService->verifyTransaction($transaction->reference);

        if ($paystackData === null) {
            return back()->withErrors([
                'error' => __('Failed to contact Paystack API to verify reference: :ref', ['ref' => $transaction->reference]),
            ]);
        }

        $status = $paystackData['status'] ?? null;

        if ($status === 'success') {
            $pointTransaction = $this->walletService->finalizePaystackDeposit(
                $transaction->user,
                $transaction->reference,
                $paystackData
            );

            if ($pointTransaction !== null) {
                return back()->with('success', __('Transaction resolved successfully. User account credited.'));
            }

            return back()->with('success', __('Transaction was already processed.'));
        }

        if ($status === 'failed') {
            $transaction->update(['status' => 'failed']);

            return back()->withErrors([
                'error' => __('Paystack reported transaction as failed.'),
            ]);
        }

        return back()->with('info', __('Paystack reports transaction is still :status.', ['status' => $status]));
    }
}
