<?php

namespace App\Enums;

enum ChatSenderType: string
{
    case CUSTOMER = 'customer';
    case AGENT = 'agent';
    case SYSTEM = 'system';

    public function label(): string
    {
        return match ($this) {
            self::CUSTOMER => 'Customer',
            self::AGENT => 'Agent',
            self::SYSTEM => 'System',
        };
    }
}
