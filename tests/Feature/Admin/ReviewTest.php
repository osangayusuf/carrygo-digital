<?php

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
});

test('admin can view reviews list', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Review::factory()->count(3)->create();

    $response = $this->get(route('admin.reviews.index'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Reviews/Index')
        ->has('reviews.data')
    );
});

test('admin can toggle review visibility status', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $review = Review::factory()->create(['is_visible' => true]);

    $response = $this->post(route('admin.reviews.toggle-visibility', $review));
    $response->assertRedirect();

    $review->refresh();
    expect($review->is_visible)->toBeFalse();

    $response = $this->post(route('admin.reviews.toggle-visibility', $review));
    $response->assertRedirect();

    $review->refresh();
    expect($review->is_visible)->toBeTrue();
});

test('public home feed only contains visible reviews', function () {
    $visibleReview = Review::factory()->create(['is_visible' => true]);
    $hiddenReview = Review::factory()->create(['is_visible' => false]);

    $response = $this->get(route('home'));
    $response->assertOk();

    $reviews = Review::visible()->get();
    expect($reviews->contains('id', $visibleReview->id))->toBeTrue();
    expect($reviews->contains('id', $hiddenReview->id))->toBeFalse();
});

test('admin can delete a review', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $review = Review::factory()->create();

    $response = $this->delete(route('admin.reviews.destroy', $review));
    $response->assertRedirect();

    $this->assertDatabaseMissing('reviews', [
        'id' => $review->id,
    ]);
});
