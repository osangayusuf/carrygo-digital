<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class HowToPlayController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('HowToPlay/Index', [
            'steps' => [
                [
                    'title' => 'Create Your Account',
                    'description' => 'Register with your details and verify your account so you can start bidding on luxury items.',
                    'icon' => 'person_add',
                ],
                [
                    'title' => 'Fund Wallet or Earn Points',
                    'description' => 'Top up your wallet and claim available bonuses, then complete tasks to grow your bidding points.',
                    'icon' => 'account_balance_wallet',
                ],
                [
                    'title' => 'Track Items Opening',
                    'description' => 'Watch item progress in Trending and Event Items until they move to Open Bids.',
                    'icon' => 'monitoring',
                ],
                [
                    'title' => 'Bid Before Timer Ends',
                    'description' => 'Place strategic bids while the countdown is active and stay alert for final-minute competition.',
                    'icon' => 'gavel',
                ],
                [
                    'title' => 'Win, Confirm, and Receive',
                    'description' => 'When the timer closes, the bidder with the highest cumulative total wins. In case of a tie, the bidder who reached that total first is the winner.',
                    'icon' => 'emoji_events',
                ],
                [
                    'title' => 'Enjoy 100% Free Delivery',
                    'description' => 'Every single product you win is delivered directly to your doorstep at absolutely zero extra shipping cost.',
                    'icon' => 'local_shipping',
                ],
            ],
            'sections' => [
                [
                    'title' => 'Open Bids',
                    'description' => 'Join auctions currently live and actively counting down.',
                    'route' => 'open-bids',
                    'icon' => 'bolt',
                ],
                [
                    'title' => 'Trending',
                    'description' => 'Discover high-interest items gaining attention from bidders.',
                    'route' => 'trending',
                    'icon' => 'trending_up',
                ],
                [
                    'title' => 'Event Items',
                    'description' => 'Follow event-based item drops and limited-time opportunities.',
                    'route' => 'event-items',
                    'icon' => 'event',
                ],
                [
                    'title' => 'Winners',
                    'description' => 'See recently completed wins and proof of successful bidding outcomes.',
                    'route' => 'winners',
                    'icon' => 'workspace_premium',
                ],
                [
                    'title' => 'Leaderboard',
                    'description' => 'Track top performers and the most competitive bidder activity.',
                    'route' => 'leaderboard',
                    'icon' => 'leaderboard',
                ],
                [
                    'title' => 'Tasks',
                    'description' => 'Complete simple actions to earn extra points and stay competitive.',
                    'route' => 'tasks',
                    'icon' => 'task_alt',
                ],
                [
                    'title' => 'Wallet',
                    'description' => 'Manage deposits, bonuses, and your bidding point balance.',
                    'route' => 'wallet',
                    'icon' => 'payments',
                ],
            ],
            'faqs' => [
                [
                    'question' => 'Do I need to be logged in to bid?',
                    'answer' => 'Yes. You can browse publicly, but placing bids requires a verified account.',
                ],
                [
                    'question' => 'How do I improve my winning chances?',
                    'answer' => 'Fund early, follow Open Bids closely, and time your bids while monitoring the live timer.',
                ],
                [
                    'question' => 'How are winners contacted?',
                    'answer' => 'Bidora reaches winners through registered profile details, so keep your phone and account information updated.',
                ],
                [
                    'question' => 'How are ties resolved when the countdown ends?',
                    'answer' => 'If multiple bidders share the same highest cumulative point total when the timer expires, the bidder who reached that total first is declared the winner.',
                ],
            ],
            'support' => [
                'title' => 'Fair Play and Delivery Tips',
                'description' => 'Use a stable internet connection, avoid last-second delays, and keep your profile details accurate to prevent prize-claim issues.',
            ],
        ]);
    }
}
