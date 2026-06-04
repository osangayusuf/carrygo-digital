<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'user']);
});

test('guests are redirected to the login page when accessing admin pages', function () {
    $response = $this->get(route('admin.users.index'));
    $response->assertRedirect(route('login'));
});

test('non-admin users are forbidden from accessing admin pages', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    $response = $this->get(route('admin.users.index'));
    $response->assertForbidden();
});

test('admin users can access admin pages', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $response = $this->get(route('admin.users.index'));
    $response->assertOk();
});

test('disabled admin users are blocked by EnsureUserActive middleware', function () {
    $admin = User::factory()->create(['is_active' => false]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $response = $this->get(route('admin.users.index'));
    $response->assertRedirect(route('login'));
});
