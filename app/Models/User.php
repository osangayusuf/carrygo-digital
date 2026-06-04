<?php

namespace App\Models;

use App\Enums\AgentStatus as AgentStatusEnum;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'points_balance', 'bonus_points', 'password', 'is_active', 'department', 'employee_id', 'agent_approved_at', 'agent_rejected_at', 'approved_by'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    protected $attributes = [
        'is_active' => true,
    ];

    protected $appends = ['is_admin'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'points_balance' => 'integer',
            'bonus_points' => 'integer',
            'checkin_streak' => 'integer',
            'last_checkin_date' => 'date',
            'spins_balance' => 'integer',
            'is_active' => 'boolean',
            'agent_approved_at' => 'datetime',
            'agent_rejected_at' => 'datetime',
        ];
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function socialProviders(): HasMany
    {
        return $this->hasMany(SocialProvider::class);
    }

    public function auctions(): HasMany
    {
        return $this->hasMany(Auction::class, 'winner_id');
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function userActivities(): HasMany
    {
        return $this->hasMany(UserActivity::class);
    }

    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function leaderboardSnapshots(): HasMany
    {
        return $this->hasMany(LeaderboardSnapshot::class);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->isAdmin();
    }

    public function isAgent(): bool
    {
        return $this->hasRole('agent');
    }

    /**
     * True if the agent has registered but not yet been approved or rejected.
     */
    public function isPendingApproval(): bool
    {
        return $this->isAgent()
            && $this->agent_approved_at === null
            && $this->agent_rejected_at === null;
    }

    /**
     * True if the agent registration has been approved by an admin.
     */
    public function isApprovedAgent(): bool
    {
        return $this->isAgent() && $this->agent_approved_at !== null;
    }

    /** The admin who approved or rejected this agent account. */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** Tickets this user raised as a customer. */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'customer_id');
    }

    /** Tickets assigned to this user as an agent. */
    public function agentTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'agent_id');
    }

    /** Chat sessions this user participated in as a customer. */
    public function chatSessions(): HasMany
    {
        return $this->hasMany(ChatSession::class, 'customer_id');
    }

    /** Chat sessions assigned to this user as an agent. */
    public function agentChatSessions(): HasMany
    {
        return $this->hasMany(ChatSession::class, 'agent_id');
    }

    /** The agent's current online/away/offline status record. */
    public function agentStatus(): HasOne
    {
        return $this->hasOne(AgentStatus::class);
    }

    /**
     * Resolve or create the agent status record and return the current status enum.
     */
    public function currentAgentStatus(): AgentStatusEnum
    {
        return $this->agentStatus?->status ?? AgentStatusEnum::OFFLINE;
    }
}
