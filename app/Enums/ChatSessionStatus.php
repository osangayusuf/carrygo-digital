<?php

namespace App\Enums;

enum ChatSessionStatus: string
{
    case WAITING = 'waiting';
    case ACTIVE = 'active';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::WAITING => 'Waiting',
            self::ACTIVE => 'Active',
            self::CLOSED => 'Closed',
        };
    }
}
