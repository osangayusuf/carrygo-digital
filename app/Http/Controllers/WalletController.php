<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Http\Requests\Wallet\ClaimBonusRequest;
use App\Http\Requests\Wallet\DepositRequest;
use App\Http\Resources\PointTransactionResource;
use App\Services\ActivityService;
use App\Services\PaystackService;
use App\Services\WalletPageService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WalletController extends Controller
{
    public function __construct(
        private readonly WalletPageService $walletPageService,
        private readonly WalletService $walletService,
        private readonly PaystackService $paystackService,
        private readonly ActivityService $activityService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Wallet/Index', [
            'balances' => $this->walletPageService->balances($user),
            'walletConfig' => $this->walletPageService->walletConfig(),
            'transactions' => PointTransactionResource::collection(
                $this->walletPageService->paginateTransactions($user),
            ),
            'paymentStatus' => $request->query('payment'),
            'paymentReference' => $request->query('reference'),
        ]);
    }

    public function deposit(DepositRequest $request): RedirectResponse
    {
        $user = $request->user();
        $amount = (float) $request->validated('amount');

        $callbackUrl = route('wallet.payment.callback');

        $data = $this->paystackService->initializeTransaction($user, $amount, $callbackUrl);

        if ($data === null) {
            return back()->withErrors([
                'amount' => 'Unable to initialize payment. Please try again.',
            ]);
        }

        Inertia::flash('paystack_init', [
            'reference' => $data['reference'],
            'access_code' => $data['access_code'] ?? null,
            'amount_kobo' => (int) ($amount * 100),
            'email' => $user->email,
            'public_key' => config('services.paystack.public'),
        ]);

        return back();
    }

    public function paymentCallback(Request $request): RedirectResponse
    {
        $reference = $request->string('reference')->toString();

        if ($reference === '') {
            return redirect()->route('wallet', ['payment' => 'failed']);
        }

        $user = $request->user();
        $paystackData = $this->paystackService->verifyTransaction($reference);

        if ($paystackData === null || ($paystackData['status'] ?? null) !== 'success') {
            return redirect()->route('wallet', [
                'payment' => 'failed',
                'reference' => $reference,
            ]);
        }

        $metadataUserId = $paystackData['metadata']['user_id'] ?? null;

        if ($metadataUserId !== null && (int) $metadataUserId !== $user->id) {
            abort(403);
        }

        $transaction = $this->walletService->finalizePaystackDeposit($user, $reference, $paystackData);

        if ($transaction !== null && $transaction->wasRecentlyCreated) {
            $this->activityService->log(ActivityType::POINTS_DEPOSITED, $user, $transaction);
        }

        return redirect()->route('wallet', [
            'payment' => 'success',
            'reference' => $reference,
        ]);
    }

    public function claimBonus(ClaimBonusRequest $request): RedirectResponse
    {
        $user = $request->user();
        $bonusAmount = (float) $user->bonus_points;

        $transaction = $this->walletService->claimBonusPoints($user, $bonusAmount);

        $this->activityService->log(ActivityType::BONUS_POINTS_CLAIMED, $user, $transaction, [
            'bonus_claimed' => $bonusAmount,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Bonus points claimed successfully.'),
        ]);

        return redirect()->route('wallet');
    }
}
