<?php

use App\Enums\AgentStatus as AgentStatusEnum;
use App\Events\Support\AgentStatusChanged;
use App\Models\AgentStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

test('TrackAgentActivity middleware updates last_activity_at and marks agent online', function () {
    Event::fake();

    $agent = User::factory()->approvedAgent()->create();
    $agent->assignRole('agent');

    // Create initial status as AWAY
    $status = AgentStatus::create([
        'user_id' => $agent->id,
        'status' => AgentStatusEnum::AWAY,
        'manual_override' => false,
        'last_activity_at' => now()->subMinutes(15),
    ]);

    // Send a request to support dashboard to trigger the middleware
    $response = $this->actingAs($agent)
        ->get(route('support.dashboard'));

    $response->assertOk();

    $freshStatus = $status->fresh();
    expect($freshStatus->status)->toBe(AgentStatusEnum::ONLINE)
        ->and($freshStatus->last_activity_at)->not->toBeNull()
        ->and($freshStatus->last_activity_at->gt(now()->subSeconds(5)))->toBeTrue();

    Event::assertDispatched(AgentStatusChanged::class, function ($e) use ($agent) {
        return $e->user->id === $agent->id && $e->status === AgentStatusEnum::ONLINE;
    });
});

test('scheduled command transitions inactive agents to AWAY and OFFLINE when manual_override is false', function () {
    Event::fake();

    $agent1 = User::factory()->approvedAgent()->create();
    $agent1->assignRole('agent');
    $status1 = AgentStatus::create([
        'user_id' => $agent1->id,
        'status' => AgentStatusEnum::ONLINE,
        'manual_override' => false,
        'last_activity_at' => now()->subMinutes(12), // >10 mins, should be AWAY
    ]);

    $agent2 = User::factory()->approvedAgent()->create();
    $agent2->assignRole('agent');
    $status2 = AgentStatus::create([
        'user_id' => $agent2->id,
        'status' => AgentStatusEnum::ONLINE,
        'manual_override' => false,
        'last_activity_at' => now()->subMinutes(35), // >30 mins, should be OFFLINE
    ]);

    $agent3 = User::factory()->approvedAgent()->create();
    $agent3->assignRole('agent');
    $status3 = AgentStatus::create([
        'user_id' => $agent3->id,
        'status' => AgentStatusEnum::ONLINE,
        'manual_override' => false,
        'last_activity_at' => now()->subMinutes(5), // <10 mins, should stay ONLINE
    ]);

    $this->artisan('support:mark-idle-agents')->assertExitCode(0);

    expect($status1->fresh()->status)->toBe(AgentStatusEnum::AWAY)
        ->and($status2->fresh()->status)->toBe(AgentStatusEnum::OFFLINE)
        ->and($status3->fresh()->status)->toBe(AgentStatusEnum::ONLINE);

    Event::assertDispatched(AgentStatusChanged::class, function ($e) use ($agent1) {
        return $e->user->id === $agent1->id && $e->status === AgentStatusEnum::AWAY;
    });

    Event::assertDispatched(AgentStatusChanged::class, function ($e) use ($agent2) {
        return $e->user->id === $agent2->id && $e->status === AgentStatusEnum::OFFLINE;
    });
});

test('scheduled command does not transition agents when manual_override is true', function () {
    Event::fake();

    $agent = User::factory()->approvedAgent()->create();
    $agent->assignRole('agent');
    $status = AgentStatus::create([
        'user_id' => $agent->id,
        'status' => AgentStatusEnum::ONLINE,
        'manual_override' => true,
        'last_activity_at' => now()->subMinutes(40), // >30 mins, but manual_override=true
    ]);

    $this->artisan('support:mark-idle-agents')->assertExitCode(0);

    expect($status->fresh()->status)->toBe(AgentStatusEnum::ONLINE);
    Event::assertNotDispatched(AgentStatusChanged::class);
});

test('manual status override updates database status and manual_override to true', function () {
    Event::fake();

    $agent = User::factory()->approvedAgent()->create();
    $agent->assignRole('agent');

    $status = AgentStatus::create([
        'user_id' => $agent->id,
        'status' => AgentStatusEnum::ONLINE,
        'manual_override' => false,
        'last_activity_at' => now(),
    ]);

    $response = $this->actingAs($agent)
        ->post(route('support.chat.status'), [
            'status' => 'away',
        ]);

    $response->assertRedirect();

    $freshStatus = $status->fresh();
    expect($freshStatus->status)->toBe(AgentStatusEnum::AWAY)
        ->and($freshStatus->manual_override)->toBeTrue();

    Event::assertDispatched(AgentStatusChanged::class, function ($e) use ($agent) {
        return $e->user->id === $agent->id && $e->status === AgentStatusEnum::AWAY;
    });
});
