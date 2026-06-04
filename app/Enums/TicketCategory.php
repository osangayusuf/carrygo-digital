<?php

namespace App\Enums;

enum TicketCategory: string
{
    case BILLING = 'billing';
    case AUCTION_DISPUTE = 'auction_dispute';
    case TECHNICAL = 'technical';
    case ACCOUNT = 'account';
    case GENERAL = 'general';

    public function label(): string
    {
        return match ($this) {
            self::BILLING => 'Billing',
            self::AUCTION_DISPUTE => 'Auction Dispute',
            self::TECHNICAL => 'Technical Issue',
            self::ACCOUNT => 'Account Help',
            self::GENERAL => 'General',
        };
    }
}
