<?php

namespace App\Models;

use App\Enums\AuctionStatus;
use Database\Factories\AuctionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'category',
    'name',
    'price',
    'description',
    'opening_points',
    'current_points',
    'status',
    'enabled',
    'event',
    'image',
    'external_url',
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
            'countdown_duration_seconds' => 'integer',
            'opening_points' => 'integer',
            'current_points' => 'integer',
            'bid_count' => 'integer',
            'status' => AuctionStatus::class,
            'enabled' => 'boolean',
            'event' => 'boolean',
            'triggered_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the auction's image URL.
     */
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => blank($value)
                ? null
                : (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')
                    ? $value
                    : Storage::disk('public')->url($value)),
        );
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    /**
     * Restrict the query to auctions that are enabled (visible to regular users).
     *
     * @param  Builder<Auction>  $query
     * @return Builder<Auction>
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    /**
     * @param  Builder<Auction>  $query
     * @return Builder<Auction>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $like = '%'.addcslashes($term, '%_\\').'%';

        return $query->where(function (Builder $q) use ($like): void {
            $q->where('name', 'like', $like)
                ->orWhere('category', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }
}
