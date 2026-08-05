<?php

use App\Models\Auction;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('user model correctly reports terms acceptance status', function () {
    $userWithoutTerms = User::factory()->create(['terms_accepted_at' => null]);
    $userWithTerms = User::factory()->create(['terms_accepted_at' => now()]);

    expect($userWithoutTerms->hasAcceptedTerms())->toBeFalse();
    expect($userWithTerms->hasAcceptedTerms())->toBeTrue();
});

test('public terms page renders successfully with documents', function () {
    $response = $this->get(route('terms.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Terms/Index')
            ->has('documents')
        );
});

test('public terms show page renders specific document content successfully', function () {
    $response = $this->get(route('terms.show', 'terms-of-use'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Terms/Show')
            ->has('current_document')
            ->has('documents')
        );
});

test('authenticated user can accept terms via endpoint', function () {
    $user = User::factory()->create(['terms_accepted_at' => null]);

    $this->actingAs($user)
        ->post(route('terms.accept'))
        ->assertRedirect();

    expect($user->fresh()->hasAcceptedTerms())->toBeTrue();
});

test('prevents user without accepted terms from placing a bid', function () {
    $user = User::factory()->create([
        'points_balance' => 100,
        'terms_accepted_at' => null,
    ]);
    $auction = Auction::factory()->active()->create();

    $this->actingAs($user)
        ->from(route('home'))
        ->post(route('auctions.bids.store', $auction), [
            'points' => 10,
        ])
        ->assertRedirect(route('home'))
        ->assertSessionHasErrors('terms');
});

test('allows user with accepted terms to place a bid', function () {
    $user = User::factory()->create([
        'points_balance' => 100,
        'terms_accepted_at' => now(),
    ]);
    $auction = Auction::factory()->active()->create();

    $this->actingAs($user)
        ->from(route('home'))
        ->post(route('auctions.bids.store', $auction), [
            'points' => 10,
        ])
        ->assertRedirect(route('home'))
        ->assertSessionHasNoErrors();

    expect($user->fresh()->points_balance)->toBe(90);
});

test('prevents user without accepted terms from claiming bonus points in wallet', function () {
    $user = User::factory()->create([
        'bonus_points' => 50,
        'terms_accepted_at' => null,
    ]);

    $this->actingAs($user)
        ->from(route('wallet'))
        ->post(route('wallet.claim-bonus'))
        ->assertRedirect(route('wallet'))
        ->assertSessionHasErrors('terms');
});

test('prevents user without accepted terms from claiming task center rewards', function () {
    $user = User::factory()->create([
        'terms_accepted_at' => null,
    ]);

    $this->actingAs($user)
        ->from(route('tasks'))
        ->post(route('rewards.claim'))
        ->assertRedirect(route('tasks'))
        ->assertSessionHasErrors('terms');
});

test('allows user without accepted terms to spin the wheel', function () {
    $user = User::factory()->create([
        'spins_balance' => 3,
        'terms_accepted_at' => null,
    ]);

    $response = $this->actingAs($user)
        ->postJson(route('spin'));

    $response->assertOk()
        ->assertJsonStructure(['points_won', 'segment_index']);
});
