<?php

use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\AuctionController as AdminAuctionController;
use App\Http\Controllers\Admin\PaystackTransactionController;
use App\Http\Controllers\Admin\PointTransactionController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\RewardsConfigController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuctionController;
use App\Http\Controllers\AuctionTimelineController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\EventItemsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HowToPlayController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\OpenBidsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RewardClaimController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Controllers\SpinController;
use App\Http\Controllers\TaskCenterController;
use App\Http\Controllers\TrendingController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WinnersController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('open-bids', [OpenBidsController::class, 'index'])->name('open-bids');
Route::get('winners', [WinnersController::class, 'index'])->name('winners');
Route::get('leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');
Route::get('how-to-play', [HowToPlayController::class, 'index'])->name('how-to-play');
Route::get('auctions/{auction}', [AuctionController::class, 'show'])->name('auctions.show');
Route::get('auctions/{auction}/timeline', [AuctionTimelineController::class, 'index'])->name('auctions.timeline');
Route::get('search', [SearchController::class, 'index'])->name('search');

Route::get('auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
    ->name('auth.social.redirect')
    ->whereIn('provider', ['google', 'facebook']);

Route::get('auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->name('auth.social.callback')
    ->whereIn('provider', ['google', 'facebook']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('trending', [TrendingController::class, 'index'])->name('trending');
    Route::get('event-items', [EventItemsController::class, 'index'])->name('event-items');

    Route::inertia('dashboard', 'Dashboard')->name('dashboard'); // TODO: might remove later

    Route::get('onboarding', [OnboardingController::class, 'index'])->name('onboarding');
    Route::post('onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');

    Route::get('wallet', [WalletController::class, 'index'])->name('wallet');
    Route::get('wallet/payment/callback', [WalletController::class, 'paymentCallback'])->name('wallet.payment.callback');
    Route::post('wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::post('wallet/claim-bonus', [WalletController::class, 'claimBonus'])->name('wallet.claim-bonus');
    Route::get('tasks', [TaskCenterController::class, 'index'])->name('tasks');
    Route::post('checkin', [CheckinController::class, 'store'])->name('checkin');
    Route::post('spin', SpinController::class)->name('spin');
    Route::post('rewards/claim', RewardClaimController::class)->name('rewards.claim');
    Route::get('profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('security', [SecurityController::class, 'edit'])->name('security.edit');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::patch('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
});

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::resource('auctions', AdminAuctionController::class)
            ->except(['show', 'create', 'edit']);
        Route::post('auctions/{auction}/publish', [AdminAuctionController::class, 'publish'])
            ->name('auctions.publish');
        Route::post('auctions/{auction}/close', [AdminAuctionController::class, 'close'])
            ->name('auctions.close');

        // Users Management
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::post('users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');

        // Agent Management (Approvals)
        Route::get('agents', [AgentController::class, 'index'])->name('agents.index');
        Route::post('agents/{user}/approve', [AgentController::class, 'approve'])->name('agents.approve');
        Route::post('agents/{user}/reject', [AgentController::class, 'reject'])->name('agents.reject');

        // Bids Audit
        Route::get('bids', [App\Http\Controllers\Admin\BidController::class, 'index'])->name('bids.index');

        // Transactions Management (Point vs Paystack)
        Route::get('point-transactions', [PointTransactionController::class, 'index'])->name('point-transactions.index');
        Route::get('paystack-transactions', [PaystackTransactionController::class, 'index'])->name('paystack-transactions.index');
        Route::post('paystack-transactions/{transaction}/requery', [PaystackTransactionController::class, 'requery'])->name('paystack-transactions.requery');

        // Reviews Moderation
        Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::post('reviews/{review}/toggle-visibility', [ReviewController::class, 'toggleVisibility'])->name('reviews.toggle-visibility');

        // Rewards Config Management
        Route::get('rewards-config', [RewardsConfigController::class, 'index'])->name('rewards-config.index');
        Route::post('rewards-config', [RewardsConfigController::class, 'update'])->name('rewards-config.update');
    });

Route::redirect('admin', '/admin/auctions', 301)->name('admin.dashboard');

Route::middleware(['auth', 'verified', 'throttle:bid'])->group(function () {
    Route::post('auctions/{auction}/bids', [BidController::class, 'store'])
        ->name('auctions.bids.store');
});

require __DIR__.'/settings.php';
require __DIR__.'/support.php';
