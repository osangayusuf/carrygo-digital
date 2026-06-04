<?php

namespace App\Enums;

enum AgentStatus: string
{
    case ONLINE = 'online';
    case AWAY = 'away';
    case OFFLINE = 'offline';

    public function label(): string
    {
        return match ($this) {
            self::ONLINE => 'Online',
            self::AWAY => 'Away',
            self::OFFLINE => 'Offline',
        };
    }
}
