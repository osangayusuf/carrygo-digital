<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('has event column in auctions table', function () {
    expect(Schema::hasColumn('auctions', 'event'))->toBeTrue();
});

it('has enabled column in auctions table defaulting to true', function () {
    expect(Schema::hasColumn('auctions', 'enabled'))->toBeTrue();

    $auction = \App\Models\Auction::factory()->create();

    expect($auction->enabled)->toBeTrue();
});
