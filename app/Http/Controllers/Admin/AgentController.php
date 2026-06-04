<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AgentApprovedNotification;
use App\Notifications\AgentRejectedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AgentController extends Controller
{
    /**
     * Display a listing of agents.
     */
    public function index(): InertiaResponse
    {
        $pending = User::role(UserRole::AGENT->value)
            ->whereNull('agent_approved_at')
            ->whereNull('agent_rejected_at')
            ->latest()
            ->get();

        $approved = User::role(UserRole::AGENT->value)
            ->whereNotNull('agent_approved_at')
            ->with('approvedBy')
            ->latest('agent_approved_at')
            ->get();

        return Inertia::render('Admin/Agents/Index', [
            'pending' => $pending,
            'approved' => $approved,
        ]);
    }

    /**
     * Approve the agent registration.
     */
    public function approve(User $user): RedirectResponse
    {
        if (! $user->isAgent()) {
            abort(403, 'User is not a support agent.');
        }

        if ($user->agent_approved_at !== null) {
            return redirect()->back()->withErrors(['error' => 'Agent is already approved.']);
        }

        $user->update([
            'agent_approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        $user->notify(new AgentApprovedNotification);

        return redirect()->back()->with('success', "Agent {$user->name} has been approved and notified.");
    }

    /**
     * Decline / Reject the agent registration and delete account.
     */
    public function reject(User $user): RedirectResponse
    {
        if (! $user->isAgent()) {
            abort(403, 'User is not a support agent.');
        }

        if ($user->agent_approved_at !== null) {
            return redirect()->back()->withErrors(['error' => 'Cannot reject an already approved agent.']);
        }

        // Send email synchronously before deleting database record
        $user->notify(new AgentRejectedNotification($user->name));

        // Delete account
        $user->delete();

        return redirect()->back()->with('success', 'Agent registration has been rejected and account deleted.');
    }
}
