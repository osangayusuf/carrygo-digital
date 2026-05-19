<?php

namespace App\Enums;

enum AuctionTimelineEntryType: string
{
    case BidPlaced = 'bid_placed';
    case AuctionTriggered = 'auction_triggered';
    case LeaderChanged = 'leader_changed';
    case AuctionClosed = 'auction_closed';
}
