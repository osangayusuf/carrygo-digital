<?php

use App\Http\Controllers\Admin\AuctionController as AdminAuctionController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\TrendingController;
use App\Http\Controllers\OpenBidsController;
use App\Http\Controllers\EventItemsController;
use App\Http\Controllers\WinnersController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\HowToPlayController;
use App\Http\Controllers\AuctionController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProfileController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('trending', [TrendingController::class, 'index'])->name('trending');
Route::get('open-bids', [OpenBidsController::class, 'index'])->name('open-bids');
Route::get('event-items', [EventItemsController::class, 'index'])->name('event-items');
Route::get('winners', [WinnersController::class, 'index'])->name('winners');
Route::get('leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');
Route::get('how-to-play', [HowToPlayController::class, 'index'])->name('how-to-play');
Route::get('auctions/{auction}', [AuctionController::class, 'show'])->name('auctions.show');
Route::get('search', [SearchController::class, 'index'])->name('search');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard'); // TODO: might remove later

    Route::get('wallet', [WalletController::class, 'index'])->name('wallet');
    Route::post('wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::post('wallet/claim-bonus', [WalletController::class, 'claimBonus'])->name('wallet.claim-bonus');
    Route::get('tasks', [TaskController::class, 'index'])->name('tasks');
    Route::get('profile', [ProfileController::class, 'index'])->name('profile');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
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
    });

Route::middleware(['auth', 'verified', 'throttle:bid'])->group(function () {
    Route::post('auctions/{auction}/bids', [BidController::class, 'store'])
        ->name('auctions.bids.store');
});

require __DIR__.'/settings.php';
