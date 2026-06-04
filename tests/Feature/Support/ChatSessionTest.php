<?php

use App\Enums\AgentStatus as AgentStatusEnum;
use App\Enums\ChatSessionStatus;
use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Events\Support\AgentClaimedSession;
use App\Events\Support\ChatMessageSent;
use App\Events\Support\ChatSessionClosed;
use App\Events\Support\NewChatSessionCreated;
use App\Models\AgentStatus;
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
