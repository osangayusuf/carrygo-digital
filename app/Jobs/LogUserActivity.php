<?php

namespace App\Jobs;

use App\Enums\ActivityType;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LogUserActivity implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ActivityType $type,
        public readonly ?User $user = null,
        public readonly ?string $subjectType = null,
        public readonly ?int $subjectId = null,
        public readonly array $metadata = [],
        public readonly ?string $ipAddress = null,
    ) {}

    public function handle(): void
    {
        UserActivity::create([
            'user_id' => $this->user?->id,
            'type' => $this->type,
            'subject_type' => $this->subjectType,
            'subject_id' => $this->subjectId,
            'metadata' => $this->metadata ?: null,
            'ip_address' => $this->ipAddress,
        ]);
    }
}
