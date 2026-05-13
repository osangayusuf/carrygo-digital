<?php

use App\Models\User;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can process deposit and convert naira to points', function () {
    $user = User::factory()->create(['points_balance' => 0]);
    $service = new WalletService;

    // Default rate is 100 points per naira
    config(['points.points_per_naira' => 100]);

    $transaction = $service->processDeposit($user, 1000, 'ref_123');

    expect($transaction->user_id)->toBe($user->id)
        ->and($transaction->type->value)->toBe('deposit')
        ->and($transaction->naira_amount)->toEqual(1000)
        ->and($transaction->amount)->toEqual(100000) // 1000 * 100
        ->and($transaction->provider_reference)->toBe('ref_123')
        ->and($transaction->status->value)->toBe('completed');
});

it('can award bonus points', function () {
    $user = User::factory()->create(['bonus_points' => 0]);
    $service = new WalletService();

    $transaction = $service->awardBonusPoints($user, 200, ['reason' => 'signup']);

    expect($transaction->user_id)->toBe($user->id)
        ->and($transaction->type->value)->toBe('bonus_award')
        ->and($transaction->amount)->toEqual(200)
        ->and($transaction->status->value)->toBe('completed')
        ->and($transaction->metadata['reason'])->toBe('signup');

    // User balance should be updated
    expect($user->fresh()->bonus_points)->toEqual(200);
});

it('can claim bonus points to spendable points', function () {
    config(['points.bonus_conversion_rate' => 1.0]);
    $user = User::factory()->create(['bonus_points' => 500, 'points_balance' => 0]);
    $service = new WalletService();

    $transaction = $service->claimBonusPoints($user, 200);

    expect($transaction->user_id)->toBe($user->id)
        ->and($transaction->type->value)->toBe('bonus_claim')
        ->and($transaction->amount)->toEqual(200)
        ->and($transaction->status->value)->toBe('completed')
        ->and($transaction->metadata['bonus_claimed'])->toEqual(200);

    $user->refresh();
    expect($user->bonus_points)->toEqual(300)
        ->and($user->points_balance)->toEqual(200); // 0 + 200
});

it('throws exception when claiming more bonus points than available', function () {
    $user = User::factory()->create([
        'points_balance' => 0,
        'bonus_points' => 50,
    ]);

    $service = new WalletService;

    $service->claimBonusPoints($user, 100);
})->throws(InvalidArgumentException::class);
