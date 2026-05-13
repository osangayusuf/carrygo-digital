<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'event',
    'reference',
    'payload',
    'ip_address',
    'status',
    'error_message',
])]
class PaystackWebhookLog extends Model
{
    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }
}
