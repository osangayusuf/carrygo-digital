<?php

use App\Enums\AgentStatus as AgentStatusEnum;
use App\Enums\ChatSenderType;
use App\Enums\ChatSessionStatus;
use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Events\Support\AgentClaimedSession;
use App\Events\Support\ChatMessageSent;
use App\Events\Support\ChatSessionClosed;
use App\Events\Support\NewChatSessionCreated;
use App\Models\AgentStatus;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

test('status returns online if at least one agent is online, offline otherwise', function () {
    // Initially no agents are online
    $this->get(route('support.chat.api.status'))
        ->assertOk()
        ->assertJson(['online' => false]);

    // Create an agent and set status to online
    $agent = User::factory()->approvedAgent()->create();
    $agent->assignRole('agent');

    AgentStatus::create([
        'user_id' => $agent->id,
        'status' => AgentStatusEnum::ONLINE,
        'manual_override' => true,
    ]);

    $this->get(route('support.chat.api.status'))
        ->assertOk()
        ->assertJson(['online' => true]);
});

test('customers and guests can initiate a chat session', function () {
    Event::fake();

    // Guest initiation
    $guestResponse = $this->post(route('support.chat.api.initiate'), [
        'name' => 'Guest User',
        'email' => 'guest@example.com',
    ]);

    $guestResponse->assertOk()
        ->assertJsonStructure(['uuid', 'customer_name', 'customer_email', 'status'])
        ->assertJson([
            'customer_name' => 'Guest User',
            'customer_email' => 'guest@example.com',
            'status' => 'waiting',
        ]);

    $guestUuid = $guestResponse->json('uuid');
    expect(session('active_chat_session'))->toBe($guestUuid);

    $this->assertDatabaseHas('chat_sessions', [
        'uuid' => $guestUuid,
        'status' => 'waiting',
    ]);

    // Customer (authenticated user) initiation
    $customer = User::factory()->create();
    $customer->assignRole('user');

    $customerResponse = $this->actingAs($customer)
        ->post(route('support.chat.api.initiate'));

    $customerResponse->assertOk()
        ->assertJson([
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'status' => 'waiting',
        ]);

    Event::assertDispatched(NewChatSessionCreated::class);
});

test('unauthorized users cannot access or send messages to a chat session', function () {
    $guestSession = ChatSession::factory()->guest()->create([
        'customer_name' => 'Guest User',
        'customer_email' => 'guest@example.com',
        'status' => ChatSessionStatus::WAITING,
    ]);

    // Try without session token or auth
    $this->get(route('support.chat.api.messages', $guestSession->uuid))
        ->assertForbidden();

    $this->post(route('support.chat.api.send', $guestSession->uuid), ['body' => 'Hello'])
        ->assertForbidden();

    // Try as another customer
    $otherCustomer = User::factory()->create();
    $this->actingAs($otherCustomer)
        ->get(route('support.chat.api.messages', $guestSession->uuid))
        ->assertForbidden();

    // Try with guest session token set in php session
    $this->withSession(['active_chat_session' => $guestSession->uuid])
        ->get(route('support.chat.api.messages', $guestSession->uuid))
        ->assertOk();
});

test('customers/guests can send and retrieve messages', function () {
    Event::fake();

    $customer = User::factory()->create();
    $session = ChatSession::factory()->create([
        'customer_id' => $customer->id,
        'customer_name' => $customer->name,
        'customer_email' => $customer->email,
        'status' => ChatSessionStatus::WAITING,
    ]);

    $this->actingAs($customer)
        ->post(route('support.chat.api.send', $session->uuid), [
            'body' => 'First message from customer',
        ])
        ->assertOk();

    $this->assertDatabaseHas('chat_messages', [
        'chat_session_id' => $session->id,
        'sender_id' => $customer->id,
        'sender_type' => 'customer',
        'body' => 'First message from customer',
    ]);

    Event::assertDispatched(ChatMessageSent::class);

    // Retrieve messages
    $response = $this->actingAs($customer)
        ->get(route('support.chat.api.messages', $session->uuid));

    $response->assertOk()
        ->assertJsonStructure(['status', 'agent', 'messages'])
        ->assertJsonCount(1, 'messages')
        ->assertJson([
            'status' => 'waiting',
            'messages' => [
                [
                    'body' => 'First message from customer',
                    'sender_type' => 'customer',
                ],
            ],
        ]);
});

test('submitting offline contact form creates a ticket', function () {
    // Guest submission
    $response = $this->post(route('support.chat.api.offline-ticket'), [
        'name' => 'Guest Ticket Submitter',
        'email' => 'guest_submit@example.com',
        'body' => 'Description of guest offline inquiry details.',
    ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    $guestUser = User::where('email', 'guest_submit@example.com')->first();
    expect($guestUser)->not->toBeNull();

    $this->assertDatabaseHas('tickets', [
        'customer_id' => $guestUser->id,
        'status' => 'open',
        'priority' => 'normal',
        'category' => 'general',
    ]);

    $this->assertDatabaseHas('ticket_messages', [
        'sender_id' => $guestUser->id,
        'body' => 'Description of guest offline inquiry details.',
    ]);
});

test('approved agents can claim, chat, close and convert sessions', function () {
    Event::fake();

    $agent = User::factory()->approvedAgent()->create();
    $agent->assignRole('agent');

    $customer = User::factory()->create();
    $session = ChatSession::factory()->create([
        'customer_id' => $customer->id,
        'customer_name' => $customer->name,
        'customer_email' => $customer->email,
        'status' => ChatSessionStatus::WAITING,
    ]);

    // Claim
    $this->actingAs($agent)
        ->post(route('support.chat.claim', $session))
        ->assertRedirect(route('support.chat.show', $session));

    expect($session->fresh()->status)->toBe(ChatSessionStatus::ACTIVE)
        ->and($session->fresh()->agent_id)->toBe($agent->id);

    Event::assertDispatched(AgentClaimedSession::class);

    // Chat
    $this->actingAs($agent)
        ->post(route('support.chat.message', $session), [
            'body' => 'Agent response',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('chat_messages', [
        'chat_session_id' => $session->id,
        'sender_id' => $agent->id,
        'sender_type' => 'agent',
        'body' => 'Agent response',
    ]);

    // Convert to Ticket
    $this->actingAs($agent)
        ->post(route('support.chat.convert', $session), [
            'subject' => 'Ticket from Chat',
            'category' => TicketCategory::BILLING->value,
            'priority' => TicketPriority::HIGH->value,
        ])
        ->assertRedirect(); // Redirects to ticket show page

    $freshSession = $session->fresh();
    expect($freshSession->ticket_id)->not->toBeNull();

    $this->assertDatabaseHas('tickets', [
        'id' => $freshSession->ticket_id,
        'subject' => 'Ticket from Chat',
        'category' => TicketCategory::BILLING->value,
        'priority' => TicketPriority::HIGH->value,
    ]);

    // Close
    $this->actingAs($agent)
        ->post(route('support.chat.close', $session))
        ->assertRedirect();

    expect($freshSession->fresh()->status)->toBe(ChatSessionStatus::CLOSED);
    Event::assertDispatched(ChatSessionClosed::class);
});

test('converting a guest chat session to a ticket provisions a customer account', function () {
    Event::fake();

    $agent = User::factory()->approvedAgent()->create();
    $agent->assignRole('agent');

    $session = ChatSession::factory()->guest()->create([
        'customer_name' => 'Guest User',
        'customer_email' => 'guest_convert@example.com',
        'agent_id' => $agent->id,
        'status' => ChatSessionStatus::ACTIVE,
    ]);

    // Guest sends a message before the agent converts the session to a ticket.
    $this->withSession(['active_chat_session' => $session->uuid])
        ->post(route('support.chat.api.send', $session->uuid), [
            'body' => 'How do I purchase points?',
        ])
        ->assertOk();

    $this->actingAs($agent)
        ->post(route('support.chat.convert', $session), [
            'subject' => 'Inquiry on how to purchase points',
            'category' => TicketCategory::AUCTION_DISPUTE->value,
            'priority' => TicketPriority::HIGH->value,
        ])
        ->assertRedirect();

    $guestUser = User::where('email', 'guest_convert@example.com')->first();
    expect($guestUser)->not->toBeNull();
    expect($session->fresh()->customer_id)->toBe($guestUser->id);

    $ticket = $session->fresh()->ticket;
    expect($ticket)->not->toBeNull()
        ->and($ticket->customer_id)->toBe($guestUser->id);

    $this->assertDatabaseHas('ticket_messages', [
        'ticket_id' => $ticket->id,
        'sender_id' => $guestUser->id,
        'body' => 'How do I purchase points?',
    ]);
});

test('converting a fully anonymous guest session without an email fails gracefully', function () {
    $agent = User::factory()->approvedAgent()->create();
    $agent->assignRole('agent');

    $session = ChatSession::factory()->create([
        'customer_id' => null,
        'customer_name' => 'Anonymous',
        'customer_email' => null,
        'agent_id' => $agent->id,
        'status' => ChatSessionStatus::ACTIVE,
    ]);

    $this->actingAs($agent)
        ->post(route('support.chat.convert', $session), [
            'subject' => 'Some inquiry',
            'category' => TicketCategory::GENERAL->value,
            'priority' => TicketPriority::NORMAL->value,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('error');

    expect($session->fresh()->ticket_id)->toBeNull();
});

test('agents can update status manually', function () {
    $agent = User::factory()->approvedAgent()->create();
    $agent->assignRole('agent');

    $this->actingAs($agent)
        ->post(route('support.chat.status'), [
            'status' => AgentStatusEnum::AWAY->value,
        ])
        ->assertRedirect();

    $status = AgentStatus::where('user_id', $agent->id)->first();
    expect($status)->not->toBeNull()
        ->and($status->status)->toBe(AgentStatusEnum::AWAY)
        ->and($status->manual_override)->toBeTrue();
});

test('agent email address is hidden and name is displayed as Agent FirstName to customers', function () {
    $agent = User::factory()->approvedAgent()->create([
        'name' => 'Musa Ibrahim',
        'email' => 'musa.agent@bidora.test',
    ]);
    $agent->assignRole('agent');

    $customer = User::factory()->create([
        'name' => 'Customer John',
        'email' => 'john.customer@example.com',
    ]);

    $session = ChatSession::factory()->create([
        'customer_id' => $customer->id,
        'customer_name' => $customer->name,
        'customer_email' => $customer->email,
        'agent_id' => $agent->id,
        'status' => ChatSessionStatus::ACTIVE,
    ]);

    $message = ChatMessage::create([
        'chat_session_id' => $session->id,
        'sender_id' => $agent->id,
        'sender_type' => ChatSenderType::AGENT,
        'body' => 'Hello, how can I help you?',
    ]);

    // Test ChatMessageSent event broadcast payload
    $event = new ChatMessageSent($message);
    $payload = $event->broadcastWith();

    expect($payload['sender'])->toHaveKey('name', 'Agent Musa')
        ->and($payload['sender'])->not->toHaveKey('email');

    // Test AgentClaimedSession event broadcast payload
    $claimEvent = new AgentClaimedSession($session);
    $claimPayload = $claimEvent->broadcastWith();
    expect($claimPayload['agent_name'])->toBe('Agent Musa');

    // Test GET messages API endpoint
    $response = $this->actingAs($customer)
        ->get(route('support.chat.api.messages', $session->uuid));

    $response->assertOk();
    $json = $response->json();

    expect($json['agent'])->toHaveKey('name', 'Agent Musa')
        ->and($json['agent'])->not->toHaveKey('email')
        ->and($json['messages'][0]['sender'])->toHaveKey('name', 'Agent Musa')
        ->and($json['messages'][0]['sender'])->not->toHaveKey('email');
});

test('user agent_display_name formats names as Agent FirstName correctly', function () {
    $agent1 = new User(['name' => 'Musa Ibrahim']);
    $agent2 = new User(['name' => 'Esther Okon']);
    $agent3 = new User(['name' => 'Agent Esther']);
    $agent4 = new User(['name' => 'Support Agent Jane']);

    expect($agent1->agent_display_name)->toBe('Agent Musa')
        ->and($agent2->agent_display_name)->toBe('Agent Esther')
        ->and($agent3->agent_display_name)->toBe('Agent Esther')
        ->and($agent4->agent_display_name)->toBe('Agent Jane');
});
