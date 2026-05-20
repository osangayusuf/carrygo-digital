<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\PaystackTransaction;
use App\Models\PointTransaction;
use App\Models\User;
use App\Notifications\BonusPointsAwarded;
use App\Notifications\BonusPointsClaimed;
use App\Notifications\PaymentConfirmed;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WalletService
{
    /**
     * Process a user deposit, converting Naira to Points.
     */
    public function processDeposit(User $user, float $nairaAmount, string $reference, array $metadata = [], ?int $paystackTransactionId = null): PointTransaction
    {
        $existing = PointTransaction::query()
            ->where('provider_reference', $reference)
            ->where('type', TransactionType::DEPOSIT)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return DB::transaction(function () use ($user, $nairaAmount, $reference, $metadata, $paystackTransactionId) {
            $exchangeRate = (float) config('points.points_per_naira');
            $pointsAmount = $nairaAmount * $exchangeRate;

            $user = User::where('id', $user->id)->lockForUpdate()->firstOrFail();
            $user->points_balance += (int) $pointsAmount;
            $user->save();

            $transaction = PointTransaction::create([
                'user_id' => $user->id,
                'paystack_transaction_id' => $paystackTransactionId,
                'type' => TransactionType::DEPOSIT,
                'amount' => $pointsAmount,
                'naira_amount' => $nairaAmount,
                'exchange_rate' => $exchangeRate,
                'provider_reference' => $reference,
                'status' => 'completed',
                'metadata' => $metadata,
            ]);

            $user->notify(new PaymentConfirmed($transaction));

            return $transaction;
        });
    }

    /**
     * Finalize a Paystack deposit after inline payment or callback verification.
     */
    public function finalizePaystackDeposit(User $user, string $reference, array $paystackData): ?PointTransaction
    {
        $status = $paystackData['status'] ?? null;

        if ($status !== 'success') {
            return null;
        }

        $amountInKobo = (int) ($paystackData['amount'] ?? 0);
        $nairaAmount = $amountInKobo / 100;

        $paystackTransaction = PaystackTransaction::query()
            ->where('reference', $reference)
            ->where('user_id', $user->id)
            ->first();

        $transaction = $this->processDeposit(
            $user,
            $nairaAmount,
            $reference,
            $paystackData,
            $paystackTransaction?->id,
        );

        if ($paystackTransaction !== null && $paystackTransaction->status !== 'success') {
            $paystackTransaction->update([
                'status' => 'success',
                'paid_at' => now(),
                'channel' => $paystackData['channel'] ?? $paystackTransaction->channel,
            ]);
        }

        return $transaction;
    }

    /**
     * Award bonus points to a user.
     */
    public function awardBonusPoints(
        User $user,
        int $bonusAmount,
        array $metadata = [],
        bool $sendBonusAwardedNotification = true,
    ): PointTransaction {
        if ($bonusAmount <= 0) {
            throw new InvalidArgumentException('Bonus amount must be positive.');
        }

        $transaction = DB::transaction(function () use ($user, $bonusAmount, $metadata) {
            $user = User::where('id', $user->id)->lockForUpdate()->firstOrFail();
            $user->bonus_points += $bonusAmount;
            $user->save();

            return PointTransaction::create([
                'user_id' => $user->id,
                'type' => TransactionType::BONUS_AWARD,
                'amount' => $bonusAmount,
                'exchange_rate' => 1.0,
                'status' => 'completed',
                'metadata' => $metadata,
            ]);
        });

        if ($sendBonusAwardedNotification) {
            $user->notify(new BonusPointsAwarded($transaction));
        }

        return $transaction;
    }

    /**
     * Claim all bonus points, converting them to spendable points balance.
     */
    public function claimAllBonusPoints(User $user): PointTransaction
    {
        $bonusAmount = (int) $user->bonus_points;

        if ($bonusAmount <= 0) {
            throw new InvalidArgumentException('You have no bonus points to claim.');
        }

        return $this->claimBonusPoints($user, $bonusAmount);
    }

    /**
     * Claim bonus points, converting them to spendable points balance.
     */
    public function claimBonusPoints(User $user, int $bonusAmount): PointTransaction
    {
        $transaction = DB::transaction(function () use ($user, $bonusAmount) {
            $user = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            if ($user->bonus_points < $bonusAmount) {
                throw new InvalidArgumentException('Insufficient bonus points balance.');
            }

            $conversionRate = (float) config('points.bonus_conversion_rate');
            $spendablePoints = (int) floor($bonusAmount * $conversionRate);

            $user->bonus_points -= $bonusAmount;
            $user->points_balance += $spendablePoints;
            $user->save();

            return PointTransaction::create([
                'user_id' => $user->id,
                'type' => TransactionType::BONUS_CLAIM,
                'amount' => $spendablePoints,
                'exchange_rate' => $conversionRate,
                'status' => 'completed',
                'metadata' => [
                    'bonus_claimed' => $bonusAmount,
                    'conversion_rate' => $conversionRate,
                ],
            ]);
        });

        $user->notify(new BonusPointsClaimed($transaction));

        return $transaction;
    }
}
