<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgentApproved
{
    /**
     * Block access to the agent portal until an admin has approved the registration.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        // Only applies to users with the agent role
        if (! $user->isAgent()) {
            return $next($request);
        }

        // Rejected agents are logged out and shown an error at the login screen
        if ($user->agent_rejected_at !== null) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('support.login')->withErrors([
                'email' => __('Your agent registration was not approved. Please contact an administrator.'),
            ]);
        }

        // Pending agents are redirected to a holding screen — not logged out
        if ($user->agent_approved_at === null) {
            return redirect()->route('support.pending');
        }

        return $next($request);
    }
}
