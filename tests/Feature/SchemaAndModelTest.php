<?php

use App\Enums\AgentStatus;
use App\Enums\ChatSenderType;
use App\Enums\ChatSessionStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\AgentStatus as AgentStatusModel;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

// ---------------------------------------------------------------------------
// Phase 1 — Schema: columns exist on users table
// ---------------------------------------------------------------------------

test('users table has agent profile columns', function () {
    $user = User::factory()->create([
        'department' => 'Customer Success',
        'employee_id' => 'EMP-0001',
    ]);

    expect($user->fresh())
        ->department->toBe('Customer Success')
        ->employee_id->toBe('EMP-0001')
        ->agent_approved_at->toBeNull()
        ->agent_rejected_at->toBeNull()
        ->approved_by->toBeNull();
});

// ---------------------------------------------------------------------------
// Phase 2 — Models: User agent status helpers
// ---------------------------------------------------------------------------

test('isPendingApproval returns true for an unapproved agent', function () {
    $agent = User::factory()->pendingAgent()->create();
    $agent->assignRole('agent');

    expect($agent->isPendingApproval())->toBeTrue()
        ->and($agent->isApprovedAgent())->toBeFalse();
});

test('isApprovedAgent returns true after approval timestamp is set', function () {
    $agent = User::factory()->approvedAgent()->create();
    $agent->assignRole('agent');

    expect($agent->isApprovedAgent())->toBeTrue()
        ->and($agent->isPendingApproval())->toBeFalse();
});

test('isAgent returns false for regular users', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    expect($user->isAgent())->toBeFalse();
});

test('currentAgentStatus returns offline when no status record exists', function () {
    $agent = User::factory()->approvedAgent()->create();
    $agent->assignRole('agent');

    expect($agent->currentAgentStatus())->toBe(AgentStatus::OFFLINE);
});

test('currentAgentStatus reflects the persisted status', function () {
    $agent = User::factory()->approvedAgent()->create();
    $agent->assignRole('agent');

    AgentStatusModel::factory()->online()->create(['user_id' => $agent->id]);

    expect($agent->fresh()->currentAgentStatus())->toBe(AgentStatus::ONLINE);
});

// ---------------------------------------------------------------------------
// Phase 2 — Models: Ticket
// ---------------------------------------------------------------------------

test('ticket is created with uuid, default status and priority', function () {
    $ticket = Ticket::factory()->open()->create();

    expect($ticket)
        ->uuid->not->toBeEmpty()
        ->status->toBe(TicketStatus::OPEN)
        ->priority->toBe(TicketPriority::NORMAL)
        ->agent_id->toBeNull();
});

test('ticket isUnassigned helper works', function () {
    $ticket = Ticket::factory()->open()->create();
    expect($ticket->isUnassigned())->toBeTrue();

    $agent = User::factory()->approvedAgent()->create();
    $assigned = Ticket::factory()->inProgress($agent)->create();
    expect($assigned->isUnassigned())->toBeFalse();
});

test('ticket isClosed helper works', function () {
    $open = Ticket::factory()->open()->create();
    $closed = Ticket::factory()->closed()->create();

    expect($open->isClosed())->toBeFalse()
        ->and($closed->isClosed())->toBeTrue();
});

test('ticket has messages relationship', function () {
    $ticket = Ticket::factory()->open()->create();
    TicketMessage::factory()->count(3)->create(['ticket_id' => $ticket->id]);

    expect($ticket->messages)->toHaveCount(3);
});

// ---------------------------------------------------------------------------
// Phase 2 — Models: TicketMessage
// ---------------------------------------------------------------------------

test('ticket message is_internal defaults to false', function () {
    $message = TicketMessage::factory()->create();
    expect($message->is_internal)->toBeFalse();
});

test('internal ticket message is flagged correctly', function () {
    $message = TicketMessage::factory()->internal()->create();
    expect($message->is_internal)->toBeTrue();
});

// ---------------------------------------------------------------------------
// Phase 2 — Models: ChatSession
// ---------------------------------------------------------------------------

test('chat session is created with uuid and waiting status', function () {
    $session = ChatSession::factory()->waiting()->create();

    expect($session)
        ->uuid->not->toBeEmpty()
        ->status->toBe(ChatSessionStatus::WAITING)
        ->agent_id->toBeNull();
});

test('chat session status helpers work correctly', function () {
    $waiting = ChatSession::factory()->waiting()->create();
    $agent = User::factory()->approvedAgent()->create();
    $active = ChatSession::factory()->active($agent)->create();
    $closed = ChatSession::factory()->closed()->create();

    expect($waiting->isWaiting())->toBeTrue()
        ->and($active->isActive())->toBeTrue()
        ->and($closed->isClosed())->toBeTrue();
});

test('chat session can be linked to a ticket', function () {
    $ticket = Ticket::factory()->open()->create();
    $session = ChatSession::factory()->create(['ticket_id' => $ticket->id]);

    expect($session->ticket->id)->toBe($ticket->id);
});

// ---------------------------------------------------------------------------
// Phase 2 — Models: ChatMessage
// ---------------------------------------------------------------------------

test('chat message sender_type casts to enum', function () {
    $message = ChatMessage::factory()->create();
    expect($message->sender_type)->toBe(ChatSenderType::CUSTOMER);
});

test('agent chat message has correct sender_type', function () {
    $agent = User::factory()->create();
    $message = ChatMessage::factory()->fromAgent($agent)->create();

    expect($message->sender_type)->toBe(ChatSenderType::AGENT)
        ->and($message->sender_id)->toBe($agent->id);
});

// ---------------------------------------------------------------------------
// Phase 2 — Models: AgentStatus
// ---------------------------------------------------------------------------

test('agent status record belongs to a user', function () {
    $agent = User::factory()->create();
    $status = AgentStatusModel::factory()->online()->create(['user_id' => $agent->id]);

    expect($status->user->id)->toBe($agent->id)
        ->and($status->status)->toBe(AgentStatus::ONLINE)
        ->and($status->isOnline())->toBeTrue();
});

test('agent status manual_override defaults to false', function () {
    $status = AgentStatusModel::factory()->create();
    expect($status->manual_override)->toBeFalse();
});

// ---------------------------------------------------------------------------
// Phase 2 — Models: User relationships
// ---------------------------------------------------------------------------

test('user has agent tickets relationship', function () {
    $agent = User::factory()->approvedAgent()->create();
    Ticket::factory()->count(2)->create(['agent_id' => $agent->id]);

    expect($agent->agentTickets)->toHaveCount(2);
});

test('user has customer tickets relationship', function () {
    $customer = User::factory()->create();
    Ticket::factory()->count(3)->create(['customer_id' => $customer->id]);

    expect($customer->tickets)->toHaveCount(3);
});

test('user has agent chat sessions relationship', function () {
    $agent = User::factory()->approvedAgent()->create();
    ChatSession::factory()->count(2)->create(['agent_id' => $agent->id]);

    expect($agent->agentChatSessions)->toHaveCount(2);
});
