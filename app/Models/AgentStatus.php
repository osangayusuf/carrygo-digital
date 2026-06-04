<?php

namespace App\Models;

use App\Enums\AgentStatus as AgentStatusEnum;
use Database\Factories\AgentStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'status', 'last_activity_at', 'manual_override'])]
#[UseFactory(AgentStatusFactory::class)]
class AgentStatus extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AgentStatusEnum::class,
            'last_activity_at' => 'datetime',
            'manual_override' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOnline(): bool
    {
        return $this->status === AgentStatusEnum::ONLINE;
    }
}
