<?php

namespace App\Services;

use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WalletPageService
{
    /**
     * @return array<string, mixed>
     */
    public function walletConfig(): array
    {
        return [
            'points_per_naira' => (float) config('points.points_per_naira'),
            'bonus_conversion_rate' => (float) config('points.bonus_conversion_rate'),
            'min_deposit_naira' => (int) config('points.min_deposit_naira'),
            'max_deposit_naira' => (int) config('points.max_deposit_naira'),
            'deposit_presets' => config('points.deposit_presets', [1000]),
            'paystack_public_key' => config('services.paystack.public'),
        ];
    }

    /**
     * @return array{points_balance: float, bonus_points: float}
     */
    public function balances(User $user): array
    {
        return [
            'points_balance' => (float) $user->points_balance,
            'bonus_points' => (float) $user->bonus_points,
        ];
    }

    /**
     * @return LengthAwarePaginator<int, PointTransaction>
     */
    public function paginateTransactions(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return PointTransaction::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
