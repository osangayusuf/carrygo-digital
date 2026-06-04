<?php

namespace App\Services;

use App\Models\User;

class OnboardingPageService
{
    public function __construct(
        private readonly WalletPageService $walletPageService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildProps(User $user): array
    {
        return [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'points_balance' => (int) $user->points_balance,
            ],
            'balances' => $this->walletPageService->balances($user),
            'walletConfig' => $this->walletPageService->walletConfig(),
            'pointsPerNaira' => (float) config('points.points_per_naira'),
            'minBidIncrement' => (int) config('points.min_bid_increment'),
        ];
    }
}
