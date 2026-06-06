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
     */
    public function addReply(Ticket $ticket, User $sender, string $body, bool $isInternal = false): TicketMessage
    {
        return DB::transaction(function () use ($ticket, $sender, $body, $isInternal): TicketMessage {
            $message = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_id' => $sender->id,
                'body' => $body,
                'is_internal' => $isInternal,
            ]);

            if ($ticket->status === TicketStatus::OPEN && ! $isInternal && $sender->id === $ticket->agent_id) {
                $ticket->update(['status' => TicketStatus::IN_PROGRESS]);
            }

            // Send TicketMessageReceivedNotification if customer (or non-assigned agent/admin) replies
            // and it is not an internal note, and there is an assigned agent, and the sender is not that agent.
            if (! $isInternal && $ticket->agent_id !== null && (int) $sender->id !== (int) $ticket->agent_id) {
                $assignedAgent = User::find($ticket->agent_id);
                if ($assignedAgent) {
                    $assignedAgent->notify(new TicketMessageReceivedNotification($message));
                }
            }

            return $message;
        });
    }

    /**
     * Create a ticket from a chat session.
     */
    public function createFromChat(ChatSession $session, array $data): Ticket
    {
        return DB::transaction(function () use ($session, $data): Ticket {
            $ticket = Ticket::create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $session->customer_id,
                'agent_id' => $session->agent_id,
                'status' => TicketStatus::OPEN,
                'priority' => $data['priority'] ?? TicketPriority::NORMAL,
                'category' => $data['category'],
                'subject' => $data['subject'],
            ]);

            $session->update(['ticket_id' => $ticket->id]);

            // Import chat message history
            foreach ($session->messages as $chatMessage) {
                // Ensure sender exists as a user before attributing it to ticket messages.
                // If it is a guest message, the sender might be null. We use the customer user or fallback.
                $sender = $chatMessage->sender ?? User::find($session->customer_id);
                if ($sender) {
                    $this->addReply($ticket, $sender, $chatMessage->body, false);
                }
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
