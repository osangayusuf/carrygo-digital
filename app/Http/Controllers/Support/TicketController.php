<?php

namespace App\Http\Controllers\Support;

use App\Enums\ActivityType;
use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Services\ActivityService;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService,
        private readonly ActivityService $activityService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $tab = $request->input('tab', 'unassigned');

        $query = Ticket::query()->with(['customer:id,name,email', 'agent:id,name']);

        if (! $user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('agent_id')
                    ->orWhere('agent_id', $user->id);
            });
        }

        if ($tab === 'unassigned') {
            $query->whereNull('agent_id')->where('status', '!=', TicketStatus::CLOSED);
        } elseif ($tab === 'mine') {
            $query->where('agent_id', $user->id)->where('status', '!=', TicketStatus::CLOSED);
        } elseif ($tab === 'closed') {
            $query->where('status', TicketStatus::CLOSED);
            if (! $user->isAdmin()) {
                $query->where('agent_id', $user->id);
            }
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Support/Tickets/Index', [
            'tickets' => $tickets,
            'filters' => [
                'tab' => $tab,
            ],
            'counts' => Inertia::defer(fn () => [
                'unassigned' => Ticket::whereNull('agent_id')->where('status', '!=', TicketStatus::CLOSED)->count(),
                'mine' => Ticket::where('agent_id', $user->id)->where('status', '!=', TicketStatus::CLOSED)->count(),
                'closed' => $user->isAdmin()
                    ? Ticket::where('status', TicketStatus::CLOSED)->count()
                    : Ticket::where('agent_id', $user->id)->where('status', TicketStatus::CLOSED)->count(),
            ]),
            'customers' => Inertia::defer(fn () => User::where('id', '!=', $user->id)->select('id', 'name', 'email')->get()),
            'categories' => array_column(TicketCategory::cases(), 'value'),
            'priorities' => array_column(TicketPriority::cases(), 'value'),
        ]);
    }

    public function show(Ticket $ticket, Request $request): Response
    {
        $user = $request->user();

        if (! $user->isAdmin() && $ticket->agent_id !== null && $ticket->agent_id !== $user->id) {
            abort(403, 'You do not have access to this ticket.');
        }

        $ticket->load([
            'customer:id,name,email',
            'agent:id,name',
            'messages.sender:id,name,email',
        ]);

        return Inertia::render('Support/Tickets/Show', [
            'ticket' => $ticket,
            'categories' => array_column(TicketCategory::cases(), 'value'),
            'priorities' => array_column(TicketPriority::cases(), 'value'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'priority' => ['required', new Enum(TicketPriority::class)],
            'category' => ['required', new Enum(TicketCategory::class)],
            'body' => 'required|string',
        ]);

        $ticket = $this->ticketService->createManually($request->user(), $validated);

        $this->activityService->log(
            type: ActivityType::TICKET_CREATED,
            user: $request->user(),
            subject: $ticket,
            metadata: ['subject' => $ticket->subject]
        );

        return redirect()->route('support.tickets.show', $ticket)->with('success', 'Ticket created successfully.');
    }

    public function claim(Ticket $ticket, Request $request): RedirectResponse
    {
        try {
            $this->ticketService->claimTicket($ticket, $request->user());

            $this->activityService->log(
                type: ActivityType::TICKET_CLAIMED,
                user: $request->user(),
                subject: $ticket
            );

            return back()->with('success', 'Ticket claimed successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function close(Ticket $ticket, Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && $ticket->agent_id !== $user->id) {
            abort(403, 'You are not assigned to this ticket.');
        }

        $this->ticketService->closeTicket($ticket);

        $this->activityService->log(
            type: ActivityType::TICKET_CLOSED,
            user: $user,
            subject: $ticket
        );

        return back()->with('success', 'Ticket closed successfully.');
    }
}
