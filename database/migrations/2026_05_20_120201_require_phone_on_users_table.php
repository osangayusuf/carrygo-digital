<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNull('phone')
            ->orderBy('id')
            ->each(function (object $user): void {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'phone' => '0800000'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
                    ]);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 15)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 15)->nullable()->change();
        });
    }
};
