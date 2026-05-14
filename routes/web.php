<?php

use App\Http\Controllers\Admin\AuctionController as AdminAuctionController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

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
