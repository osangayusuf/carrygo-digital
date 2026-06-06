<?php

namespace App\Http\Controllers\Support;

use App\Enums\AgentStatus as AgentStatusEnum;
use App\Enums\ChatSenderType;
use App\Enums\ChatSessionStatus;
use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Events\Support\AgentStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\AgentStatus;
use App\Models\ChatSession;
use App\Services\ChatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    /**
     * Create a new ChatController instance.
     */
    public function __construct(
        private readonly ChatService $chatService
    ) {}

    /**
     * Display the support agent live chat dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $waitingSessions = ChatSession::where('status', ChatSessionStatus::WAITING)
            ->with(['customer:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->get();

        $activeSessions = ChatSession::where('status', ChatSessionStatus::ACTIVE)
            ->when(! $user->isAdmin(), fn ($q) => $q->where('agent_id', $user->id))
            ->with(['customer:id,name,email', 'agent:id,name'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $closedSessions = ChatSession::where('status', ChatSessionStatus::CLOSED)
            ->when(! $user->isAdmin(), fn ($q) => $q->where('agent_id', $user->id))
            ->with(['customer:id,name,email', 'agent:id,name'])
            ->orderBy('closed_at', 'desc')
            ->take(20)
            ->get();

        $agentStatus = AgentStatus::firstOrCreate(
            ['user_id' => $user->id],
            [
                'status' => AgentStatusEnum::OFFLINE,
                'manual_override' => false,
            ]
        );

        return Inertia::render('Support/Chat/Index', [
            'waitingSessions' => $waitingSessions,
            'activeSessions' => $activeSessions,
            'closedSessions' => $closedSessions,
            'agentStatus' => $agentStatus,
            'selectedSession' => null,
            'categories' => array_column(TicketCategory::cases(), 'value'),
            'priorities' => array_column(TicketPriority::cases(), 'value'),
        ]);
    }

    /**
     * Display a specific active or closed chat session details.
     */
    public function show(ChatSession $session, Request $request): Response
    {
        $user = $request->user();

        if (! $user->isAdmin() && $session->agent_id !== null && (int) $session->agent_id !== (int) $user->id) {
            abort(403, 'You do not have access to this chat session.');
        }

        $session->load(['customer:id,name,email', 'agent:id,name', 'messages.sender:id,name,email']);

        $waitingSessions = ChatSession::where('status', ChatSessionStatus::WAITING)
            ->with(['customer:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->get();

        $activeSessions = ChatSession::where('status', ChatSessionStatus::ACTIVE)
            ->when(! $user->isAdmin(), fn ($q) => $q->where('agent_id', $user->id))
            ->with(['customer:id,name,email', 'agent:id,name'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $closedSessions = ChatSession::where('status', ChatSessionStatus::CLOSED)
            ->when(! $user->isAdmin(), fn ($q) => $q->where('agent_id', $user->id))
            ->with(['customer:id,name,email', 'agent:id,name'])
            ->orderBy('closed_at', 'desc')
            ->take(20)
            ->get();

        $agentStatus = AgentStatus::firstOrCreate(['user_id' => $user->id], [
            'status' => AgentStatusEnum::OFFLINE,
            'manual_override' => false,
        ]);

        return Inertia::render('Support/Chat/Index', [
            'waitingSessions' => $waitingSessions,
            'activeSessions' => $activeSessions,
            'closedSessions' => $closedSessions,
            'agentStatus' => $agentStatus,
            'selectedSession' => $session,
            'categories' => array_column(TicketCategory::cases(), 'value'),
            'priorities' => array_column(TicketPriority::cases(), 'value'),
        ]);
    }

    /**
     * Claim a waiting chat session.
     */
    public function claim(ChatSession $session, Request $request): RedirectResponse
    {
        try {
            $this->chatService->claimSession($session, $request->user());

            return redirect()->route('support.chat.show', $session)->with('success', 'Chat session claimed.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Send a message to a chat session.
     */
    public function sendMessage(ChatSession $session, Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && (int) $session->agent_id !== (int) $user->id) {
            abort(403, 'You are not assigned to this chat session.');
        }

        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $this->chatService->addMessage(
            $session,
            $user,
            $validated['body'],
            ChatSenderType::AGENT
        );

        return back();
    }

    /**
     * Close a chat session.
     */
    public function close(ChatSession $session, Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && (int) $session->agent_id !== (int) $user->id) {
            abort(403, 'You are not assigned to this chat session.');
        }

        $this->chatService->closeSession($session);

        return back()->with('success', 'Chat session closed.');
    }

    /**
     * Convert a chat session to a support ticket.
     */
    public function convertToTicket(ChatSession $session, Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && (int) $session->agent_id !== (int) $user->id) {
            abort(403, 'You are not assigned to this chat session.');
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'category' => ['required', new Enum(TicketCategory::class)],
            'priority' => ['required', new Enum(TicketPriority::class)],
        ]);

        try {
            $ticket = $this->chatService->convertToTicket($session, $validated);

            return redirect()->route('support.tickets.show', $ticket)->with('success', 'Chat session successfully converted to ticket.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the manual online/away/offline status of the agent.
     */
    public function updateStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', new Enum(AgentStatusEnum::class)],
        ]);

        $status = AgentStatus::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'status' => $validated['status'],
                'manual_override' => true,
                'last_activity_at' => now(),
            ]
        );

        broadcast(new AgentStatusChanged($request->user(), $status->status));

        return back()->with('success', 'Status updated successfully.');
    }
}
