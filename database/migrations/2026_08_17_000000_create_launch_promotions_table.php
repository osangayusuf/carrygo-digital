<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('launch_promotions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->unsignedInteger('slots_total');
            $table->unsignedInteger('slots_claimed')->default(0);
            $table->unsignedInteger('amount');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed the launch-week promotion, inactive by default: first 100
        // verified users get a 5,000 point bonus once this is switched on.
        // Deliberately does NOT activate itself — flip `is_active` to true
        // (e.g. via `php artisan tinker`) at the moment you actually want
        // the promo to go live, so an earlier deploy can't start the clock
        // before launch. Toggle it back off to end the promo early.
        DB::table('launch_promotions')->insert([
            'slug' => 'launch_first_100',
            'slots_total' => 100,
            'slots_claimed' => 0,
            'amount' => 5000,
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('launch_promotions');
    }
};
