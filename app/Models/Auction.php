<?php

namespace App\Models;

use App\Enums\AuctionStatus;
use Database\Factories\AuctionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category',
    'name',
    'price',
    'description',
    'opening_points',
    'current_points',
    'status',
    'event',
    'image',
    'bid_count',
    'countdown_duration_seconds',
    'triggered_at',
    'expires_at',
    'winner_id',
])]
class Auction extends Model
{
    /** @use HasFactory<AuctionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'status' => AuctionStatus::class,
            'event' => 'boolean',
            'triggered_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }
}
