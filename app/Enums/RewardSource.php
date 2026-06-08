<?php

namespace App\Enums;

enum RewardSource: string
{
    case Checkin = 'checkin';
    case Spin = 'spin';
    case Achievement = 'achievement';
    case Leaderboard = 'leaderboard';
    case ReferralSignup = 'referral_signup';
    case ReferralDeposit = 'referral_deposit';
}
