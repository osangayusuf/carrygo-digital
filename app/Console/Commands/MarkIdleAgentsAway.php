<?php

namespace App\Console\Commands;

use App\Enums\AgentStatus as AgentStatusEnum;
use App\Events\Support\AgentStatusChanged;
use App\Models\AgentStatus;
use Illuminate\Console\Command;

class MarkIdleAgentsAway extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'support:mark-idle-agents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transition idle support agents to AWAY or OFFLINE status based on activity.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = now();
        $awayCutoff = $now->copy()->subMinutes(10);
        $offlineCutoff = $now->copy()->subMinutes(30);

        // Find agents who are ONLINE or AWAY and have manual_override = false
        $statuses = AgentStatus::where('manual_override', false)
            ->whereIn('status', [AgentStatusEnum::ONLINE, AgentStatusEnum::AWAY])
            ->with('user')
            ->get();

        foreach ($statuses as $statusRecord) {
            $lastActivity = $statusRecord->last_activity_at;

            if ($lastActivity === null) {
                // If they never had activity, mark them OFFLINE
                $this->updateStatus($statusRecord, AgentStatusEnum::OFFLINE);

                continue;
            }

            if ($lastActivity->lessThan($offlineCutoff)) {
                $this->updateStatus($statusRecord, AgentStatusEnum::OFFLINE);
            } elseif ($lastActivity->lessThan($awayCutoff) && $statusRecord->status === AgentStatusEnum::ONLINE) {
                $this->updateStatus($statusRecord, AgentStatusEnum::AWAY);
            }
        }

        return self::SUCCESS;
    }

    private function updateStatus(AgentStatus $statusRecord, AgentStatusEnum $newStatus): void
    {
        $oldStatus = $statusRecord->status;

        if ($oldStatus !== $newStatus) {
            $statusRecord->update(['status' => $newStatus]);

            // Broadcast event
            broadcast(new AgentStatusChanged($statusRecord->user, $newStatus));

            $this->info("Agent {$statusRecord->user->name} status changed from {$oldStatus->value} to {$newStatus->value}.");
        }
    }
}
