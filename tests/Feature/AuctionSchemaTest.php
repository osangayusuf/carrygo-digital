<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('has event column in auctions table', function () {
    expect(Schema::hasColumn('auctions', 'event'))->toBeTrue();
});
