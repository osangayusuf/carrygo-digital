<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Points per Naira
    |--------------------------------------------------------------------------
    | The number of spendable points awarded for every ₦1 deposited via Paystack.
    | Example: 100 means ₦1 → 100 points.
    */
    'points_per_naira' => env('POINTS_PER_NAIRA', 10),

    /*
    |--------------------------------------------------------------------------
    | Bonus Conversion Rate
    |--------------------------------------------------------------------------
    | The multiplier applied when a user claims their bonus_points balance.
    | 1.0 means 1 bonus point → 1 spendable point (1-to-1 conversion).
    */
    'bonus_conversion_rate' => env('BONUS_CONVERSION_RATE', 1.0),

    /*
    |--------------------------------------------------------------------------
    | Minimum Bid Increment
    |--------------------------------------------------------------------------
    | The minimum number of points required for a single bid placement.
    */
    'min_bid_increment' => env('MIN_BID_INCREMENT', 10),

    /*
    |--------------------------------------------------------------------------
    | Deposit Limits (Naira)
    |--------------------------------------------------------------------------
    */
    'min_deposit_naira' => env('MIN_DEPOSIT_NAIRA', 100),

    'max_deposit_naira' => env('MAX_DEPOSIT_NAIRA', 500_000),

    /*
    |--------------------------------------------------------------------------
    | Deposit Presets (Naira)
    |--------------------------------------------------------------------------
    */
    'deposit_presets' => [100, 200, 500, 1000, 5_000, 10_000],

];
