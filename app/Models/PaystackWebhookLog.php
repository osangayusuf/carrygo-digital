<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'event',
    'reference',
    'destination',
    'payload',
    'raw_body',
    'ip_address',
    'status',
    'forward_status',
    'forward_attempts',
    'forwarded_at',
    'error_message',
])]
class PaystackWebhookLog extends Model
{
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'forwarded_at' => 'datetime',
        ];
    }
}
