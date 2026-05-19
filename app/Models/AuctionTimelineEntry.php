<?php

namespace App\Models;

use App\Enums\AuctionTimelineEntryType;
use Database\Factories\AuctionTimelineEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'auction_id',
    'type',
    'user_id',
    'bid_id',
    'payload',
    'occurred_at',
])]
class AuctionTimelineEntry extends Model
{
    /** @use HasFactory<AuctionTimelineEntryFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected function casts(): array
    {
        return [
            'type' => AuctionTimelineEntryType::class,
            'payload' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bid(): BelongsTo
    {
        return $this->belongsTo(Bid::class);
    }
}
