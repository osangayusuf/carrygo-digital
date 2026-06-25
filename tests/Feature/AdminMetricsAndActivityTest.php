<?php

use App\Enums\ActivityType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\PointTransaction;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('Agent dashboard returns real-time statistics props', function () {
    $agent = User::factory()->approvedAgent()->create();
    $agent->assignRole('agent');

    $response = $this->actingAs($agent)
        ->get(route('support.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Support/Dashboard')
        ->has('stats')
        ->has('stats.openTickets')
        ->has('stats.myTickets')
        ->has('stats.activeChats')
        ->has('stats.onlineAgents')
    );
});

test('Admin metrics computes points purchased and user registrations correctly', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    // Create user registered within current month
    $user = User::factory()->create(['created_at' => now()]);
    $user->assignRole('user');

    // Create completed deposit transaction
    PointTransaction::create([
        'user_id' => $user->id,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::COMPLETED,
        'amount' => 100,
        'naira_amount' => 5000,
        'exchange_rate' => 50,
        'created_at' => now(),
    ]);

    // Create a closed auction
    $auction = Auction::factory()->closed()->create([
        'category' => 'electronics',
        'name' => 'Test Item',
        'price' => 10000.00,
        'description' => 'Test',
        'bid_count' => 15,
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.metrics.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Metrics/Index')
        ->where('metrics.totalPointsBought', 100)
        ->where('metrics.totalNairaSpent', 5000)
        ->where('metrics.newUsersCount', 1)
        ->where('metrics.completedAuctionsCount', 1)
        ->where('metrics.avgBidsPerAuction', 15)
    );
});

test('User registration logs activity via listener', function () {
    $user = User::factory()->create();

    event(new Registered($user));

    $this->assertDatabaseHas('user_activities', [
        'user_id' => $user->id,
        'type' => ActivityType::USER_REGISTERED->value,
    ]);
});

test('Admin toggling user status logs activity', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create(['is_active' => true]);

    $response = $this->actingAs($admin)
        ->post(route('admin.users.toggle-active', $user));

    $response->assertRedirect();
    $this->assertDatabaseHas('user_activities', [
        'user_id' => $admin->id,
        'type' => ActivityType::USER_STATUS_TOGGLED->value,
        'subject_id' => $user->id,
    ]);
});

test('Admin changing user role logs activity', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();

    $response = $this->actingAs($admin)
        ->post(route('admin.users.role', $user), [
            'role' => 'agent',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('user_activities', [
        'user_id' => $admin->id,
        'type' => ActivityType::USER_ROLE_CHANGED->value,
        'subject_id' => $user->id,
    ]);
});

test('ActivityLogController flags shared IP addresses (3+ users in last 24h)', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $ip = '192.168.1.100';

    // Create 3 activities for 3 different users with same IP in last 24 hours
    for ($i = 0; $i < 3; $i++) {
        $user = User::factory()->create();
        UserActivity::create([
            'user_id' => $user->id,
            'type' => ActivityType::LOGIN_SUCCESS->value,
            'ip_address' => $ip,
            'created_at' => now()->subHours(2),
        ]);
    }

    $response = $this->actingAs($admin)
        ->get(route('admin.activity-log.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/ActivityLog/Index')
        ->has('alerts.sharedIps', 1)
        ->where('alerts.sharedIps.0.ip', $ip)
        ->where('alerts.sharedIps.0.count', 3)
    );
});

test('ActivityLogController flags users exceeding bid speed limit (20+ bids/min)', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();
    $auction = Auction::factory()->active()->create([
        'category' => 'electronics',
        'name' => 'Bidding Item',
        'price' => 500.00,
        'description' => 'Speed Test',
    ]);

    // Create 20 bids for this user in the same minute
    $time = now();
    for ($i = 0; $i < 20; $i++) {
        Bid::create([
            'user_id' => $user->id,
            'auction_id' => $auction->id,
            'amount' => 10 + $i,
            'created_at' => $time,
        ]);
    }

    $response = $this->actingAs($admin)
        ->get(route('admin.activity-log.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/ActivityLog/Index')
        ->has('alerts.highBidRate', 1)
        ->where('alerts.highBidRate.0.user.id', $user->id)
        ->where('alerts.highBidRate.0.bid_count', 20)
    );
});
