<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'rank',
    'total_bid_pts',
    'week_start',
    'bonus_points_awarded',
])]
class LeaderboardSnapshot extends Model
{
    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'rank' => 'integer',
            'total_bid_pts' => 'integer',
            'bonus_points_awarded' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
