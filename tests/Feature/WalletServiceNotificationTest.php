<?php

use App\Models\User;
use App\Notifications\BonusPointsAwarded;
use App\Notifications\PaymentConfirmed;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    $this->service = app(WalletService::class);
    $this->user = User::factory()->create();
});

test('processDeposit sends a PaymentConfirmed notification', function () {
    $this->service->processDeposit($this->user, 1000, 'ref_abc123');

    Notification::assertSentTo($this->user, PaymentConfirmed::class);
});

test('PaymentConfirmed notification contains correct data', function () {
    $this->service->processDeposit($this->user, 500.00, 'ref_xyz999');

    Notification::assertSentTo(
        $this->user,
        PaymentConfirmed::class,
        function (PaymentConfirmed $notification) {
            $data = $notification->toDatabase($this->user);

            return $data['type'] === 'payment_confirmed'
                && $data['reference'] === 'ref_xyz999'
                && $data['naira_amount'] == 500.00;
        }
    );
});

test('awardBonusPoints sends a BonusPointsAwarded notification', function () {
    $this->service->awardBonusPoints($this->user, 200);

    Notification::assertSentTo($this->user, BonusPointsAwarded::class);
});

test('BonusPointsAwarded notification contains correct amount', function () {
    $this->service->awardBonusPoints($this->user, 150);

    Notification::assertSentTo(
        $this->user,
        BonusPointsAwarded::class,
        function (BonusPointsAwarded $notification) {
            $data = $notification->toDatabase($this->user);

            return $data['type'] === 'bonus_points_awarded'
                && $data['amount'] == 150;
        }
    );
});

test('claimBonusPoints does not send a BonusPointsAwarded notification', function () {
    $this->user->update(['bonus_points' => 100]);
    $this->service->claimBonusPoints($this->user, 100);

    Notification::assertNotSentTo($this->user, BonusPointsAwarded::class);
});
