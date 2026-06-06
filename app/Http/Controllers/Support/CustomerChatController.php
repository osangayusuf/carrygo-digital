<?php

namespace App\Http\Controllers\Support;

use App\Enums\ChatSenderType;
use App\Http\Controllers\Controller;
use App\Models\AgentStatus;
use App\Models\ChatSession;
use App\Models\Ticket;
use App\Models\User;
use App\Services\ChatService;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CustomerChatController extends Controller
{
    /**
     * Create a new CustomerChatController instance.
     */
    public function __construct(
        private readonly ChatService $chatService,
        private readonly TicketService $ticketService
    ) {}

    /**
     * Check if any support agent is currently online.
     */
    public function status(): JsonResponse
    {
        $anyOnline = AgentStatus::where('status', \App\Enums\AgentStatus::ONLINE)->exists();

        return response()->json([
            'online' => $anyOnline,
        ]);
    }

    /**
     * Initiate a new support chat session.
     */
    public function initiate(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
            ]);
            $guestData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];
        } else {
            $guestData = [];
        }

        $session = $this->chatService->initiate($user, $guestData);

        if (! $user) {
            // Keep track of the active session UUID in the PHP session for guest authorization
            session(['active_chat_session' => $session->uuid]);
        }

        return response()->json([
            'uuid' => $session->uuid,
            'customer_name' => $session->customer_name,
            'customer_email' => $session->customer_email,
            'status' => $session->status->value,
        ]);
    }

    /**
     * Get all messages for a given chat session (customer-facing).
     */
    public function getMessages(string $uuid, Request $request): JsonResponse
    {
        $session = ChatSession::where('uuid', $uuid)->firstOrFail();

        $this->authorizeSessionAccess($session);

        $messages = $session->messages()
            ->with('sender:id,name,email')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'status' => $session->status->value,
            'agent' => $session->agent ? [
                'id' => $session->agent->id,
                'name' => $session->agent->name,
            ] : null,
            'messages' => $messages,
        ]);
    }

    /**
     * Send a new message to the chat session (customer-facing).
     */
    public function sendMessage(string $uuid, Request $request): JsonResponse
    {
        $session = ChatSession::where('uuid', $uuid)->firstOrFail();

        $this->authorizeSessionAccess($session);

        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $message = $this->chatService->addMessage(
            $session,
            Auth::user(),
            $validated['body'],
            ChatSenderType::CUSTOMER
        );

        $message->load('sender:id,name,email');

        return response()->json($message);
    }

    /**
     * Validate that the current user or guest session is authorized to access the chat session.
     */
    private function authorizeSessionAccess(ChatSession $session): void
    {
        $user = Auth::user();

        // Agents/admins can access any session
        if ($user && ($user->isAdmin() || $user->isApprovedAgent())) {
            return;
        }

        // Authenticated customer must match the session owner
        if ($session->customer_id !== null) {
            if ($user && (int) $user->id === (int) $session->customer_id) {
                return;
            }
            abort(403, 'Unauthorized access to this chat session.');
        }

        // Guest session UUID must match the one stored in PHP session
        if (session('active_chat_session') === $session->uuid) {
            return;
        }

        abort(403, 'Unauthorized access to this chat session.');
    }

    /**
     * Submit a support ticket when all agents are offline.
     */
    public function submitOfflineTicket(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'body' => 'required|string',
            ]);

            // Find or create guest user
            $customer = User::firstOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['name'],
                    'password' => bcrypt(Str::random(16)),
                ]
            );
            $body = $validated['body'];
        } else {
            $validated = $request->validate([
                'body' => 'required|string',
            ]);
            $customer = $user;
            $body = $validated['body'];
        }

        $this->ticketService->createFromOffline($customer, $body);

        return response()->json([
            'success' => true,
            'message' => 'Your support ticket has been created successfully.',
        ]);
    }
}
