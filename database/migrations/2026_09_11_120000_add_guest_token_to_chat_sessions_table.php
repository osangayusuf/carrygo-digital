<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            // Lets an anonymous guest be identified across page reloads and browser restarts
            // (via a long-lived cookie) so they can see and resume all of their past chat
            // sessions, not just the single most recent one tracked in the PHP session.
            $table->string('guest_token')->nullable()->after('customer_email')->index();
        });
    }

    public function down(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->dropColumn('guest_token');
        });
    }
};
