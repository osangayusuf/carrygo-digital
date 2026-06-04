<?php

use App\Enums\TicketStatus;
use App\Events\Support\TicketMessageAdded;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

test('unapproved agents cannot access tickets index', function () {
    $agent = User::factory()->pendingAgent()->create();
    $agent->assignRole('agent');

    $this->actingAs($agent)
        ->get(route('support.tickets.index'))
        ->assertRedirect(route('support.pending'));
});

test('approved agents can view tickets index and list unassigned tickets', function () {
    $agent = User::factory()->approvedAgent()->create();
    $agent->assignRole('agent');

    $customer = User::factory()->create();
    $customer->assignRole('user');

    $ticket = Ticket::factory()->open()->create([
        'customer_id' => $customer->id,
        'subject' => 'Unassigned Ticket Test',
    ]);

    $response = $this->actingAs($agent)
        ->get(route('support.tickets.index', ['tab' => 'unassigned']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Support/Tickets/Index')
        ->has('tickets.data', 1)
        ->where('tickets.data.0.subject', 'Unassigned Ticket Test')
    );
});

test('agents can only view their own assigned tickets and unassigned tickets', function () {
    $agent1 = User::factory()->approvedAgent()->create();
    $agent1->assignRole('agent');

    $agent2 = User::factory()->approvedAgent()->create();
    $agent2->assignRole('agent');

    $customer = User::factory()->create();

    // Ticket 1: Assigned to Agent 1
    $ticket1 = Ticket::factory()->inProgress($agent1)->create([
        'customer_id' => $customer->id,
        'subject' => 'Ticket 1',
    ]);

    // Ticket 2: Assigned to Agent 2
    $ticket2 = Ticket::factory()->inProgress($agent2)->create([
        'customer_id' => $customer->id,
        'subject' => 'Ticket 2',
    ]);

    // Agent 1 views Index (tab=mine)
    $response = $this->actingAs($agent1)
        ->get(route('support.tickets.index', ['tab' => 'mine']));

    $response->assertInertia(fn ($page) => $page
        ->has('tickets.data', 1)
        ->where('tickets.data.0.subject', 'Ticket 1')
    );

    // Agent 1 should be forbidden/unauthorized from viewing Ticket 2
    $this->actingAs($agent1)
        ->get(route('support.tickets.show', $ticket2))
        ->assertForbidden();
});

test('admins can view all tickets', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $agent = User::factory()->approvedAgent()->create();
    $agent->assignRole('agent');

    $customer = User::factory()->create();

    $ticket = Ticket::factory()->inProgress($agent)->create([
        'customer_id' => $customer->id,
        'subject' => 'Ticket for Agent',
    ]);

    $this->actingAs($admin)
        ->get(route('support.tickets.show', $ticket))
        ->assertOk();
});

test('agents can claim an unassigned ticket', function () {
    $agent = User::factory()->approvedAgent()->create();
    $agent->assignRole('agent');

    $customer = User::factory()->create();

    $ticket = Ticket::factory()->open()->create([
        'customer_id' => $customer->id,
        'subject' => 'Unassigned Ticket',
    ]);

    $response = $this->actingAs($agent)
        ->patch(route('support.tickets.claim', $ticket));

    $response->assertRedirect();
    expect($ticket->fresh()->agent_id)->toBe($agent->id)
        ->and($ticket->fresh()->status)->toBe(TicketStatus::IN_PROGRESS);
});

test('agents cannot claim an already assigned ticket', function () {
    $agent1 = User::factory()->approvedAgent()->create();
    $agent1->assignRole('agent');

    $agent2 = User::factory()->approvedAgent()->create();
    $agent2->assignRole('agent');

    $customer = User::factory()->create();

    $ticket = Ticket::factory()->inProgress($agent1)->create([
        'customer_id' => $customer->id,
        'subject' => 'Assigned Ticket',
    ]);

    $response = $this->actingAs($agent2)
        ->patch(route('support.tickets.claim', $ticket));

    $response->assertRedirect();
    $response->assertSessionHasErrors('error');
    expect($ticket->fresh()->agent_id)->toBe($agent1->id);
});

test('agents can reply to a ticket and write internal notes', function () {
    Event::fake();

    $agent = User::factory()->approvedAgent()->create();
    $agent->assignRole('agent');

    $customer = User::factory()->create();

    $ticket = Ticket::factory()->inProgress($agent)->create([
        'customer_id' => $customer->id,
        'subject' => 'Ticket Subject',
    ]);

    // Public Reply
    $response = $this->actingAs($agent)
        ->post(route('support.tickets.messages.store', $ticket), [
            'body' => 'Public response message',
            'is_internal' => false,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('ticket_messages', [
        'ticket_id' => $ticket->id,
        'body' => 'Public response message',
        'is_internal' => false,
    ]);

    Event::assertDispatched(TicketMessageAdded::class);

    // Internal Note
    $response = $this->actingAs($agent)
        ->post(route('support.tickets.messages.store', $ticket), [
            'body' => 'Internal note text',
            'is_internal' => true,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('ticket_messages', [
        'ticket_id' => $ticket->id,
        'body' => 'Internal note text',
        'is_internal' => true,
    ]);
});

test('agents can close a ticket', function () {
    $agent = User::factory()->approvedAgent()->create();
    $agent->assignRole('agent');

    $customer = User::factory()->create();

    $ticket = Ticket::factory()->inProgress($agent)->create([
        'customer_id' => $customer->id,
        'subject' => 'Ticket to Close',
    ]);

    $response = $this->actingAs($agent)
        ->patch(route('support.tickets.close', $ticket));

    $response->assertRedirect();
    expect($ticket->fresh()->status)->toBe(TicketStatus::CLOSED)
        ->and($ticket->fresh()->closed_at)->not->toBeNull();
});
