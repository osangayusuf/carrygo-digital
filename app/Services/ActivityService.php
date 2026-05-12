<?php

namespace App\Services;

use App\Enums\ActivityType;
use App\Jobs\LogUserActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ActivityService
{
    public function __construct(private readonly Request $request) {}

    /**
     * Dispatch an activity log entry.
     *
     * In testing/local environments the job runs synchronously so assertions
     * can inspect the database immediately without a queue worker.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function log(
        ActivityType $type,
        ?User $user = null,
        ?Model $subject = null,
        array $metadata = [],
    ): void {
        $job = new LogUserActivity(
            type: $type,
            user: $user,
            subjectType: $subject ? $subject::class : null,
            subjectId: $subject?->getKey(),
            metadata: array_merge(
                ['user_agent' => $this->request->userAgent()],
                $metadata,
            ),
            ipAddress: $this->request->ip(),
        );

        if (App::environment(['testing', 'local'])) {
            $job->handle();
        } else {
            dispatch($job);
        }
    }
}
