<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'provider_name', 'provider_id', 'token', 'refresh_token', 'expires_at'])]
#[Hidden(['token', 'refresh_token'])]
class SocialProvider extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the social provider.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
