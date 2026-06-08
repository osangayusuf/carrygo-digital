<?php

namespace App\Listeners;

use App\Services\WalletService;
use Illuminate\Auth\Events\Verified;

class AwardWelcomePointsOnVerification
{
    public function __construct(protected WalletService $walletService) {}

    /**
     * Handle the event.
     */
    public function handle(Verified $event): void
    {
        $this->walletService->awardWelcomePoints($event->user);
    }
}
