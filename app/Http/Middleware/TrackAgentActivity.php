<?php

namespace App\Http\Middleware;

use App\Enums\AgentStatus as AgentStatusEnum;
use App\Events\Support\AgentStatusChanged;
use App\Models\AgentStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackAgentActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->hasRole('agent') || $user->hasRole('admin'))) {
            $statusRecord = AgentStatus::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'status' => AgentStatusEnum::ONLINE,
                    'manual_override' => false,
                ]
            );

            $oldStatus = $statusRecord->status;
            $newStatus = $oldStatus;

            // If manual_override is false, force status back to ONLINE if it wasn't
            if (! $statusRecord->manual_override) {
                $newStatus = AgentStatusEnum::ONLINE;
            }

            $statusRecord->update([
                'last_activity_at' => now(),
                'status' => $newStatus,
            ]);

            if ($oldStatus !== $newStatus) {
                broadcast(new AgentStatusChanged($user, $newStatus))->toOthers();
            }
        }

        return $next($request);
    }
}
