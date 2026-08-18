<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'slots_total', 'slots_claimed', 'amount', 'is_active'])]
class LaunchPromotion extends Model
{
    /**
     * Slug for the "first 100 verified users" launch-week bonus.
     */
    public const FIRST_HUNDRED_SLUG = 'launch_first_100';

    protected function casts(): array
    {
        return [
            'slots_total' => 'integer',
            'slots_claimed' => 'integer',
            'amount' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function hasSlotsRemaining(): bool
    {
        return $this->is_active && $this->slots_claimed < $this->slots_total;
    }

    public function slotsRemaining(): int
    {
        return max(0, $this->slots_total - $this->slots_claimed);
    }
}
