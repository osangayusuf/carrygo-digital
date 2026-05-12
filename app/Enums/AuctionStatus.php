<?php

namespace App\Enums;

enum AuctionStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case TRIGGERED = 'triggered';
    case CLOSED = 'closed';
}
