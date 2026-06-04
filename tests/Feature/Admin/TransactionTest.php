<?php

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\PaystackTransaction;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
});

test('admin can view point transactions ledger', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $user = User::factory()->create();
    PointTransaction::create([
        'user_id' => $user->id,
        'type' => TransactionType::DEPOSIT,
        'amount' => 500,
        'naira_amount' => 5000,
        'exchange_rate' => 0.1,
        'provider_reference' => 'ref_123',
        'status' => TransactionStatus::COMPLETED,
    ]);

    $response = $this->get(route('admin.point-transactions.index'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/PointTransactions/Index')
        ->has('transactions.data')
    );
});

test('admin can view paystack transactions ledger', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $user = User::factory()->create();
    PaystackTransaction::create([
        'user_id' => $user->id,
        'reference' => 'paystack_ref_123',
        'amount' => 500000, // ₦5000 in kobo
        'status' => 'pending',
        'currency' => 'NGN',
    ]);

    $response = $this->get(route('admin.paystack-transactions.index'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/PaystackTransactions/Index')
        ->has('transactions.data')
    );
});

test('admin can requery and resolve a pending paystack transaction', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $user = User::factory()->create();
    $transaction = PaystackTransaction::create([
        'user_id' => $user->id,
        'reference' => 'paystack_ref_123',
        'amount' => 500000, // ₦5000
        'status' => 'pending',
        'currency' => 'NGN',
    ]);

    // Mock Paystack transaction verification API call
    Http::fake([
        'api.paystack.co/transaction/verify/paystack_ref_123' => Http::response([
            'status' => true,
            'data' => [
                'status' => 'success',
                'reference' => 'paystack_ref_123',
                'amount' => 500000,
                'currency' => 'NGN',
            ],
        ], 200),
    ]);

    $response = $this->post(route('admin.paystack-transactions.requery', $transaction));
    $response->assertRedirect();
    $response->assertSessionHas('success');

    $transaction->refresh();
    expect($transaction->status)->toBe('success');

    // Confirm that the user's wallet point transaction was created and balance updated
    $this->assertDatabaseHas('point_transactions', [
        'user_id' => $user->id,
        'provider_reference' => 'paystack_ref_123',
        'status' => TransactionStatus::COMPLETED->value,
    ]);
});
