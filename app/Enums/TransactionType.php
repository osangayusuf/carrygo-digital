<?php

namespace App\Enums;

enum TransactionType: string
{
    case DEPOSIT = 'deposit';
    case BID_DEBIT = 'bid_debit';
    case BONUS_AWARD = 'bonus_award';
    case BONUS_CLAIM = 'bonus_claim';
}
