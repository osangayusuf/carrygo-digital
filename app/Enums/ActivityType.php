<?php

namespace App\Enums;

enum ActivityType: string
{
    case LOGIN_SUCCESS = 'login_success';
    case LOGIN_FAILED = 'login_failed';
    case PROFILE_UPDATED = 'profile_updated';
    case PASSWORD_CHANGED = 'password_changed';
    case TWO_FACTOR_ENABLED = 'two_factor_enabled';
    case TWO_FACTOR_DISABLED = 'two_factor_disabled';
    case AUCTION_VIEWED = 'auction_viewed';
    case BID_PLACED = 'bid_placed';
    case AUCTION_WON = 'auction_won';
    case POINTS_DEPOSITED = 'points_deposited';
    case POINTS_SPENT = 'points_spent';
    case BONUS_POINTS_CLAIMED = 'bonus_points_claimed';
}
