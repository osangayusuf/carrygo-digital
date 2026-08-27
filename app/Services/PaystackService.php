<?php

namespace App\Services;

use App\Models\PaystackTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    /**
     * Verify the Paystack webhook signature.
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        $signature = $request->header('x-paystack-signature');

        if (! $signature) {
            return false;
        }

        $secret = config('services.paystack.secret');
        $computedSignature = hash_hmac('sha512', $request->getContent(), $secret);

        return hash_equals($computedSignature, $signature);
    }

    /**
     * Initialize a Paystack transaction.
     */
    public function initializeTransaction(User $user, float $amount, ?string $callbackUrl = null): ?array
    {
        $secret = config('services.paystack.secret');

        $response = Http::withToken($secret)
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => $user->email,
                'amount' => (int) ($amount * 100), // Convert Naira to Kobo
                'callback_url' => $callbackUrl,
                'metadata' => [
                    'app' => 'bidora',
                    'user_id' => $user->id,
                ],
            ]);

        if ($response->successful()) {
            $data = $response->json('data');

            PaystackTransaction::create([
                'user_id' => $user->id,
                'reference' => $data['reference'],
                'access_code' => $data['access_code'] ?? null,
                'amount' => (int) ($amount * 100),
                'status' => 'pending',
                'currency' => 'NGN',
            ]);

            return $data;
        }

        Log::error('Paystack initialize transaction failed', [
            'status' => $response->status(),
            'body' => $response->body(),
            'user_id' => $user->id,
        ]);

        return null;
    }

    /**
     * Verify a transaction manually via Paystack API.
     */
    public function verifyTransaction(string $reference): ?array
    {
        $secret = config('services.paystack.secret');

        $response = Http::withToken($secret)
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        if ($response->successful()) {
            return $response->json('data');
        }

        Log::error('Paystack verify transaction failed', [
            'status' => $response->status(),
            'body' => $response->body(),
            'reference' => $reference,
        ]);

        return null;
    }
}
