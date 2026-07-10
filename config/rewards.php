<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Daily Check-in
    |--------------------------------------------------------------------------
    | Amounts are bonus points (unclaimed until converted via bonus_conversion_rate).
    */
    'checkin' => [
        'base_points' => 5,
        'milestones' => [
            7 => 25,
            14 => 50,
            30 => 100,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Spin Wheel
    |--------------------------------------------------------------------------
    | Probabilities must sum to 100.
    */
    'spin_wheel' => [
        'daily_grant' => 1,
        'segments' => [
            ['points' => 5, 'probability' => 40],
            ['points' => 10, 'probability' => 30],
            ['points' => 25, 'probability' => 20],
            ['points' => 50, 'probability' => 10],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Achievements
    |--------------------------------------------------------------------------
    */
    'achievements' => [
        'first_bid' => [
            'label' => 'First Bid',
            'description' => 'Place your first bid on any auction.',
            'icon' => 'gavel',
            'points' => 10,
            'target' => 1,
        ],
        'first_win' => [
            'label' => 'First Win',
            'description' => 'Win your first auction.',
            'icon' => 'emoji_events',
            'points' => 50,
            'target' => 1,
        ],
        'explorer' => [
            'label' => 'Explorer',
            'description' => 'Bid in three different auction categories.',
            'icon' => 'explore',
            'points' => 30,
            'target' => 3,
        ],
        'big_spender' => [
            'label' => 'Big Spender',
            'description' => 'Spend 500 points bidding in total.',
            'icon' => 'payments',
            'points' => 75,
            'target' => 500,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Weekly Leaderboard Bonuses (bonus points by rank)
    |--------------------------------------------------------------------------
    */
    'weekly_leaderboard' => [
        'top_ranks' => 10,
        'bonuses' => [
            1 => 200,
            2 => 100,
            3 => 50,
            4 => 25,
            5 => 20,
            6 => 15,
            7 => 10,
            8 => 10,
            9 => 5,
            10 => 5,
        ],
    ],

];
