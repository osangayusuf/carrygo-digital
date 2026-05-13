<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'reference',
    'access_code',
    'amount',
    'status',
    'channel',
    'currency',
    'paid_at',
    'metadata',
])]
class PaystackTransaction extends Model
{
    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
