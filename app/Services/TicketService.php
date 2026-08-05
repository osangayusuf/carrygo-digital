<?php

namespace App\Services;

use App\Enums\AgentStatus;
use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\ChatSession;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Notifications\NewTicketCreatedNotification;
use App\Notifications\TicketMessageReceivedNotification;
use App\Notifications\TicketReplyNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class TicketService
{
    /**
     * Create a ticket manually by an agent.
     */
    public function createManually(User $agent, array $data): Ticket
    {
        return DB::transaction(function () use ($agent, $data): Ticket {
            $ticket = Ticket::create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $data['customer_id'],
                'agent_id' => $agent->id,
                'status' => TicketStatus::IN_PROGRESS,
                'priority' => $data['priority'] ?? TicketPriority::NORMAL,
                'category' => $data['category'],
                'subject' => $data['subject'],
            ]);

            if (! empty($data['body'])) {
                $this->addReply($ticket, $agent, $data['body'], false);
            }

            // Notify online agents
            $this->notifyOnlineAgentsOfNewTicket($ticket);

            return $ticket;
        });
    }

    /**
     * Claim an unassigned ticket.
     */
    public function claimTicket(Ticket $ticket, User $agent): void
    {
        if ($ticket->agent_id !== null) {
            throw new \Exception('Ticket is already claimed.');
        }

        $ticket->update([
            'agent_id' => $agent->id,
            'status' => TicketStatus::IN_PROGRESS,
        ]);
    }

    /**
     * Close a ticket.
     */
    public function closeTicket(Ticket $ticket): void
    {
        $ticket->update([
            'status' => TicketStatus::CLOSED,
            'closed_at' => now(),
        ]);
    }

    /**
     * Add a message (reply or internal note) to a ticket.
     *
     * $notify controls whether recipients are emailed about this message at all. It defaults
     * to true for normal replies, but is set to false when replaying chat history into a newly
     * converted ticket (see createFromChat()) — those messages were already seen live in the
     * chat widget, so notifying anyone about them again would just be noise.
     */
    public function addReply(Ticket $ticket, User $sender, string $body, bool $isInternal = false, bool $notify = true): TicketMessage
    {
        return DB::transaction(function () use ($ticket, $sender, $body, $isInternal, $notify): TicketMessage {
            $message = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_id' => $sender->id,
                'body' => $body,
                'is_internal' => $isInternal,
            ]);

            if ($ticket->status === TicketStatus::OPEN && ! $isInternal && $sender->id === $ticket->agent_id) {
                $ticket->update(['status' => TicketStatus::IN_PROGRESS]);
            }

            // Notify the assigned agent if the customer (or another agent/admin) replies:
            // not an internal note, there is an assigned agent, and the sender isn't that agent.
            if ($notify && ! $isInternal && $ticket->agent_id !== null && (int) $sender->id !== (int) $ticket->agent_id) {
                $assignedAgent = User::find($ticket->agent_id);
                if ($assignedAgent) {
                    $assignedAgent->notify(new TicketMessageReceivedNotification($message));
                }
            }

            // Notify the customer by email if support staff (or anyone other than the
            // customer) replies and it is not an internal note.
            if ($notify && ! $isInternal && (int) $sender->id !== (int) $ticket->customer_id) {
                $ticket->customer?->notify(new TicketReplyNotification($message));
            }

            return $message;
        });
    }

    /**
     * Create a ticket from a chat session.
     *
     * $customer is the resolved account to attribute the ticket to. Chat sessions allow a
     * null customer_id for guests, but tickets require a real user, so callers must resolve
     * (or provision) one first — see ChatService::resolveCustomerForConversion().
     */
    public function createFromChat(ChatSession $session, User $customer, array $data): Ticket
    {
        return DB::transaction(function () use ($session, $customer, $data): Ticket {
            $ticket = Ticket::create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $customer->id,
                'agent_id' => $session->agent_id,
                'status' => TicketStatus::OPEN,
                'priority' => $data['priority'] ?? TicketPriority::NORMAL,
                'category' => $data['category'],
                'subject' => $data['subject'],
            ]);

            $session->update(['ticket_id' => $ticket->id]);

            // Import chat message history. These were already seen live in the chat widget by
            // both parties, so replaying them as ticket messages should not send notifications.
            foreach ($session->messages as $chatMessage) {
                // Ensure sender exists as a user before attributing it to ticket messages.
                // If it is a guest message, the sender might be null. We use the resolved customer as fallback.
                $sender = $chatMessage->sender ?? $customer;
                $this->addReply($ticket, $sender, $chatMessage->body, false, notify: false);
            }

            // Notify online agents
            $this->notifyOnlineAgentsOfNewTicket($ticket);

            return $ticket;
        });
    }

    /**
     * Create a ticket from an offline support request.
     */
    public function createFromOffline(User $customer, string $body): Ticket
    {
        return DB::transaction(function () use ($customer, $body): Ticket {
            $ticket = Ticket::create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $customer->id,
                'agent_id' => null,
                'status' => TicketStatus::OPEN,
                'priority' => TicketPriority::NORMAL,
                'category' => TicketCategory::GENERAL,
                'subject' => 'Offline Support Request: '.Str::limit($body, 40),
            ]);

            $this->addReply($ticket, $customer, $body, false);

            // Notify online agents
            $this->notifyOnlineAgentsOfNewTicket($ticket);

            return $ticket;
        });
    }

    /**
     * Notify all online agents and admins of a new ticket.
     */
    private function notifyOnlineAgentsOfNewTicket(Ticket $ticket): void
    {
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

        Notification::send($onlineAgents, new NewTicketCreatedNotification($ticket));
    }
}
