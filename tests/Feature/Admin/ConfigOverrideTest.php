<?php

use App\Models\User;
use App\Providers\SettingsServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
});

test('admin can view system configuration page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $response = $this->get(route('admin.rewards-config.index'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/RewardsConfig/Index')
        ->has('config')
    );
});

test('admin can update configuration with valid 100% spin wheel probability', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $payload = [
        'points_per_naira' => 15,
        'bonus_conversion_rate' => 2.5,
        'min_bid_increment' => 50,
        'min_deposit_naira' => 100,
        'max_deposit_naira' => 10000,
        'checkin_base_points' => 5,
        'checkin_milestones' => [
            '3' => 10,
            '7' => 30,
        ],
        'spin_wheel_daily_grant' => 1,
        'spin_wheel_segments' => [
            ['points' => 10, 'probability' => 40],
            ['points' => 20, 'probability' => 60],
        ],
    ];

    $response = $this->post(route('admin.rewards-config.update'), $payload);
    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Assert DB table updated
    $this->assertDatabaseHas('settings', [
        'key' => 'points.points_per_naira',
        'value' => '15',
    ]);

    // Assert cache was cleared
    expect(Cache::has('settings.all'))->toBeFalse();

    // Verify config override works since SettingsServiceProvider loads them
    (new SettingsServiceProvider(app()))->boot();
    expect(config('points.points_per_naira'))->toBe(15);
    expect(config('points.bonus_conversion_rate'))->toBe(2.5);
    expect(config('rewards.spin_wheel.segments'))->toBe([
        ['points' => 10, 'probability' => 40],
        ['points' => 20, 'probability' => 60],
    ]);
});

test('admin cannot update configuration with invalid spin wheel probability sum', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    // Sum is 40 + 50 = 90% (must be 100%)
    $payload = [
        'points_per_naira' => 15,
        'bonus_conversion_rate' => 2.5,
        'min_bid_increment' => 50,
        'min_deposit_naira' => 100,
        'max_deposit_naira' => 10000,
        'checkin_base_points' => 5,
        'checkin_milestones' => [
            '3' => 10,
            '7' => 30,
        ],
        'spin_wheel_daily_grant' => 1,
        'spin_wheel_segments' => [
            ['points' => 10, 'probability' => 40],
            ['points' => 20, 'probability' => 50],
        ],
    ];

    $response = $this->post(route('admin.rewards-config.update'), $payload);
    $response->assertSessionHasErrors('spin_wheel_segments');

    // Verify DB was NOT updated
    $this->assertDatabaseMissing('settings', [
        'key' => 'points.points_per_naira',
        'value' => '15',
    ]);
});
