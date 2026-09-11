<?php

use App\Enums\ChatSessionStatus;
use App\Enums\TicketStatus;
use App\Http\Controllers\Auth\AgentAuthController;
use App\Http\Controllers\Support\ChatController;
use App\Http\Controllers\Support\CustomerChatController;
use App\Http\Controllers\Support\NotificationController;
use App\Http\Controllers\Support\TicketController;
use App\Http\Controllers\Support\TicketMessageController;
use App\Models\AgentStatus;
use App\Models\ChatSession;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Customer Chat Endpoints (no auth required for guest support)
Route::middleware(['web'])->prefix('support/chat-api')->group(function () {
    Route::get('status', [CustomerChatController::class, 'status'])->name('support.chat.api.status');
    Route::get('sessions', [CustomerChatController::class, 'sessions'])->name('support.chat.api.sessions');
    Route::post('initiate', [CustomerChatController::class, 'initiate'])->name('support.chat.api.initiate');
    Route::get('{uuid}/messages', [CustomerChatController::class, 'getMessages'])->name('support.chat.api.messages');
    Route::post('{uuid}/message', [CustomerChatController::class, 'sendMessage'])->name('support.chat.api.send');
    Route::post('offline-ticket', [CustomerChatController::class, 'submitOfflineTicket'])->name('support.chat.api.offline-ticket');
});

// Guest / Unauthenticated Agent Routes
Route::middleware(['web'])->group(function () {
    Route::get('support/login', [AgentAuthController::class, 'showLogin'])
        ->name('support.login');

    Route::get('support/register', [AgentAuthController::class, 'showRegister'])
        ->name('support.register');

    Route::post('support/register', [AgentAuthController::class, 'register'])
        ->name('support.register.store');
});

// Authenticated but potentially pending Agent Routes
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('support/pending', [AgentAuthController::class, 'pending'])
        ->name('support.pending');
});

// Authenticated, Verified and Approved Agent Portal Routes
Route::middleware(['web', 'auth', 'verified', 'role:agent|admin', 'agent.approved', 'agent.activity'])
    ->prefix('support')
    ->group(function () {
        Route::get('/', function (Request $request) {
            $user = $request->user();

            return Inertia\Inertia::render('Support/Dashboard', [
                'stats' => [
                    'openTickets' => Ticket::whereNull('agent_id')
                        ->where('status', '!=', TicketStatus::CLOSED)
                        ->count(),
                    'myTickets' => Ticket::where('agent_id', $user->id)
                        ->where('status', '!=', TicketStatus::CLOSED)
                        ->count(),
                    'activeChats' => ChatSession::where('status', ChatSessionStatus::ACTIVE)
                        ->when(! $user->isAdmin(), fn ($q) => $q->where('agent_id', $user->id))
                        ->count(),
                    'onlineAgents' => AgentStatus::where('status', App\Enums\AgentStatus::ONLINE)
                        ->whereHas('user', function ($q) {
                            $q->role(['agent', 'admin']);
                        })
                        ->count(),
                ],
            ]);
        })->name('support.dashboard');

        // Ticket Management
        Route::get('tickets', [TicketController::class, 'index'])->name('support.tickets.index');
        Route::post('tickets', [TicketController::class, 'store'])->name('support.tickets.store');
        Route::get('tickets/{ticket}', [TicketController::class, 'show'])->name('support.tickets.show');
        Route::patch('tickets/{ticket}/claim', [TicketController::class, 'claim'])->name('support.tickets.claim');
        Route::patch('tickets/{ticket}/close', [TicketController::class, 'close'])->name('support.tickets.close');
        Route::post('tickets/{ticket}/messages', [TicketMessageController::class, 'store'])->name('support.tickets.messages.store');

        // Live Chat Management
        Route::get('chat', [ChatController::class, 'index'])->name('support.chat.index');
        Route::post('chat/status', [ChatController::class, 'updateStatus'])->name('support.chat.status');
        Route::get('chat/{session}', [ChatController::class, 'show'])->name('support.chat.show');
        Route::post('chat/{session}/claim', [ChatController::class, 'claim'])->name('support.chat.claim');
        Route::post('chat/{session}/message', [ChatController::class, 'sendMessage'])->name('support.chat.message');
        Route::post('chat/{session}/close', [ChatController::class, 'close'])->name('support.chat.close');
        Route::post('chat/{session}/convert', [ChatController::class, 'convertToTicket'])->name('support.chat.convert');

        // Notifications Management
        Route::get('notifications', [NotificationController::class, 'index'])->name('support.notifications.index');
        Route::patch('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('support.notifications.read');
        Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('support.notifications.read-all');
    });
