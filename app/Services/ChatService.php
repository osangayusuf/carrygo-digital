<?php

namespace App\Services;

use App\Enums\AgentStatus;
use App\Enums\ChatSenderType;
use App\Enums\ChatSessionStatus;
use App\Events\Support\AgentClaimedSession;
use App\Events\Support\ChatMessageSent;
use App\Events\Support\ChatSessionClosed;
use App\Events\Support\NewChatSessionCreated;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\NewChatSessionNotification;
use App\Notifications\TicketConvertedFromChatNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class ChatService
{
    /**
     * Create a new ChatService instance.
     */
    public function __construct(
        private readonly TicketService $ticketService
    ) {}

    /**
     * Initiate a new chat session for a customer or guest.
     */
    public function initiate(?User $customer, array $guestData): ChatSession
    {
        return DB::transaction(function () use ($customer, $guestData): ChatSession {
            $session = ChatSession::create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $customer?->id,
                'customer_name' => $customer ? $customer->name : ($guestData['name'] ?? 'Guest'),
                'customer_email' => $customer ? $customer->email : ($guestData['email'] ?? null),
                'status' => ChatSessionStatus::WAITING,
            ]);

            // Broadcast that a new chat session has been created to support agents
            broadcast(new NewChatSessionCreated($session))->toOthers();

            // Send notification to all online agents/admins
            $onlineAgents = User::role(['agent', 'admin'])
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereHas('roles', fn ($r) => $r->where('name', 'agent'))
                            ->whereNotNull('agent_approved_at');
                    })->orWhereHas('roles', fn ($r) => $r->where('name', 'admin'));
                })
                ->whereHas('agentStatus', function ($query) {
                    $query->where('status', AgentStatus::ONLINE);
                })
                ->get();

            Notification::send($onlineAgents, new NewChatSessionNotification($session));

            return $session;
        });
    }

    /**
     * Claim a waiting chat session by an agent.
     */
    public function claimSession(ChatSession $session, User $agent): void
    {
        if (! $session->isWaiting()) {
            throw new \Exception('This chat session is already claimed or closed.');
        }

        DB::transaction(function () use ($session, $agent): void {
            $session->update([
                'agent_id' => $agent->id,
                'status' => ChatSessionStatus::ACTIVE,
                'started_at' => now(),
            ]);

            // Add a system message notifying that the agent joined
            $this->addMessage($session, null, "Agent {$agent->name} has joined the chat.", ChatSenderType::SYSTEM);

            // Broadcast that the session has been claimed
            broadcast(new AgentClaimedSession($session))->toOthers();
        });
    }

    /**
     * Add a message (from customer, agent, or system) to the chat session.
     */
    public function addMessage(ChatSession $session, ?User $sender, string $body, ChatSenderType $type): ChatMessage
    {
        return DB::transaction(function () use ($session, $sender, $body, $type): ChatMessage {
            $message = ChatMessage::create([
                'chat_session_id' => $session->id,
                'sender_id' => $sender?->id,
                'sender_type' => $type,
                'body' => $body,
            ]);

            // Broadcast the new message to participants
            broadcast(new ChatMessageSent($message))->toOthers();

            return $message;
        });
    }

    /**
     * Close an active or waiting chat session.
     */
    public function closeSession(ChatSession $session): void
    {
        if ($session->isClosed()) {
            return;
        }

        DB::transaction(function () use ($session): void {
            $session->update([
                'status' => ChatSessionStatus::CLOSED,
                'closed_at' => now(),
            ]);

            // Add a system message notifying that the chat is closed
            $this->addMessage($session, null, 'The chat session has been closed.', ChatSenderType::SYSTEM);

            // Broadcast that the session has been closed
            broadcast(new ChatSessionClosed($session))->toOthers();
        });
    }

    /**
     * Convert the chat session history into a support ticket.
     */
    public function convertToTicket(ChatSession $session, array $ticketData): Ticket
    {
        if ($session->ticket_id !== null) {
            throw new \Exception('This chat session has already been converted to a ticket.');
        }

        return DB::transaction(function () use ($session, $ticketData): Ticket {
            // Tickets always belong to a real user account, but chat sessions allow guests
            // (no customer_id). Resolve or provision an account before creating the ticket.
            $customer = $this->resolveCustomerForConversion($session);

            // Use the ticket service to create the ticket and import messages
            $ticket = $this->ticketService->createFromChat($session, $customer, $ticketData);

            // Set the ticket ID on the chat session
            $session->update([
                'ticket_id' => $ticket->id,
            ]);

            // Add a system message to the chat session indicating conversion
            $this->addMessage(
                $session,
                null,
                "This chat session has been converted to a support ticket: {$ticket->subject}.",
                ChatSenderType::SYSTEM
            );

            // Email the customer, since the system chat message above is only visible if
            // they're still watching the live chat widget.
            $customer->notify(new TicketConvertedFromChatNotification($ticket));

            return $ticket;
        });
    }

    /**
     * Resolve the user a converted ticket should be attributed to.
     *
     * Authenticated sessions already have a customer_id. Guest sessions don't, so we
     * find-or-create an account by the guest's email — mirroring the guest flow used for
     * offline ticket submissions (see CustomerChatController::submitOfflineTicket) — and
     * link it back onto the session so future lookups/replies attribute correctly.
     *
     * @throws \Exception if the session is a guest session with no email on file, since
     *                     there is then no identity to attach the ticket to.
     */
    private function resolveCustomerForConversion(ChatSession $session): User
    {
        if ($session->customer_id !== null) {
            return $session->customer ?? User::findOrFail($session->customer_id);
        }

        if (empty($session->customer_email)) {
            throw new \Exception('Cannot convert to a ticket: this guest has not provided an email address. Ask for their email in the chat first.');
        }

        $customer = User::firstOrCreate(
            ['email' => $session->customer_email],
            [
                'name' => $session->customer_name ?: 'Guest',
                'password' => bcrypt(Str::random(16)),
            ]
        );

        $session->update(['customer_id' => $customer->id]);

        return $customer;
    }
}
