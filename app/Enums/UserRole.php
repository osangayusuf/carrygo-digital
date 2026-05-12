<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case USER = 'user';
    case AGENT = 'agent';

    public function label(): string
    {
        return $this->value;
    }
}
