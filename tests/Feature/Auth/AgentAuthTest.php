<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

test('agent registration screen can be rendered', function () {
    $response = $this->get(route('support.register'));
    $response->assertOk();
});

test('new agent can register', function () {
    Event::fake();

    $response = $this->post(route('support.register.store'), [
        'name' => 'Agent Name',
        'email' => 'agent@carrygo.com',
        'phone' => '08031112222',
        'department' => 'Support Department',
        'employee_id' => 'EMP-AGENT-1',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $this->assertAuthenticated();

    $user = auth()->user();
    expect($user->isAgent())->toBeTrue()
        ->and($user->isPendingApproval())->toBeTrue()
        ->and($user->department)->toBe('Support Department')
        ->and($user->employee_id)->toBe('EMP-AGENT-1');

    Event::assertDispatched(Registered::class);
    $response->assertRedirect(route('verification.notice'));
});

test('unverified agent cannot access support dashboard', function () {
    $agent = User::factory()->pendingAgent()->unverified()->create();
    $agent->assignRole('agent');

    // Unverified agent goes to verification notice
    $response = $this->actingAs($agent)->get(route('support.dashboard'));
    $response->assertRedirect(route('verification.notice'));
});

test('verified but unapproved agent is redirected to pending screen', function () {
    $agent = User::factory()->pendingAgent()->create([
        'email_verified_at' => now(),
    ]);
    $agent->assignRole('agent');

    // Should redirect to pending page
    $response = $this->actingAs($agent)->get(route('support.dashboard'));
    $response->assertRedirect(route('support.pending'));
});

test('pending screen is accessible to unapproved agents', function () {
    $agent = User::factory()->pendingAgent()->create([
        'email_verified_at' => now(),
    ]);
    $agent->assignRole('agent');

    $response = $this->actingAs($agent)->get(route('support.pending'));
    $response->assertOk();
});

test('approved agent can access support dashboard', function () {
    $agent = User::factory()->approvedAgent()->create([
        'email_verified_at' => now(),
    ]);
    $agent->assignRole('agent');

    $response = $this->actingAs($agent)->get(route('support.dashboard'));
    $response->assertOk();
});

test('rejected agent is logged out and redirected to login with error', function () {
    $agent = User::factory()->create([
        'agent_approved_at' => null,
        'agent_rejected_at' => now(),
        'email_verified_at' => now(),
    ]);
    $agent->assignRole('agent');

    $response = $this->actingAs($agent)->get(route('support.dashboard'));

    $response->assertRedirect(route('support.login'));
    $this->assertGuest();
});
