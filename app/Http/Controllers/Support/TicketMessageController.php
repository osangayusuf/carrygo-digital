<?php

namespace App\Http\Controllers\Support;

use App\Enums\ActivityType;
use App\Events\Support\TicketMessageAdded;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\ActivityService;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TicketMessageController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService,
        private readonly ActivityService $activityService
    ) {}

    public function store(Ticket $ticket, Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && $ticket->agent_id !== null && $ticket->agent_id !== $user->id) {
            abort(403, 'You do not have access to this ticket.');
        }

        $validated = $request->validate([
            'body' => 'required|string',
            'is_internal' => 'sometimes|boolean',
        ]);

        $isInternal = (bool) ($validated['is_internal'] ?? false);

        $message = $this->ticketService->addReply(
            ticket: $ticket,
            sender: $user,
            body: $validated['body'],
            isInternal: $isInternal
        );

        // Fire broadcast event
        broadcast(new TicketMessageAdded($message))->toOthers();

        // Log user activity
        $this->activityService->log(
            type: ActivityType::TICKET_MESSAGE_ADDED,
            user: $user,
            subject: $ticket,
            metadata: [
                'message_id' => $message->id,
                'is_internal' => $isInternal,
            ]
        );

        return back()->with('success', $isInternal ? 'Internal note added.' : 'Reply sent successfully.');
    }
}
