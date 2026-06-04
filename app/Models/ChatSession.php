<?php

namespace App\Models;

use App\Enums\ChatSessionStatus;
use Database\Factories\ChatSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['uuid', 'customer_id', 'customer_name', 'customer_email', 'agent_id', 'ticket_id', 'status', 'started_at', 'closed_at'])]
#[UseFactory(ChatSessionFactory::class)]
class ChatSession extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (ChatSession $session): void {
            if (empty($session->uuid)) {
                $session->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ChatSessionStatus::class,
            'started_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function isWaiting(): bool
    {
        return $this->status === ChatSessionStatus::WAITING;
    }

    public function isActive(): bool
    {
        return $this->status === ChatSessionStatus::ACTIVE;
    }

    public function isClosed(): bool
    {
        return $this->status === ChatSessionStatus::CLOSED;
    }
}
