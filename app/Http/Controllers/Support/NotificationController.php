<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Services\NotificationFeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationFeedService $notificationFeed
    ) {}

    /**
     * Display the notifications index page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Fetch mapped notifications for the view
        $notifications = $this->notificationFeed->forUser($user, 50);

        return Inertia::render('Support/Notifications/Index', [
            'notificationsList' => $notifications,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
