<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('paystack_webhook_logs', function (Blueprint $table) {
            // bidora | fanscorner | unrouted (unrouted currently falls through
            // to bidora processing, but is still recorded as such for visibility).
            $table->string('destination')->nullable()->after('reference');

            // Exact raw request body, needed to forward to Fanscorner without
            // re-encoding (re-encoded JSON would break Fanscorner's own HMAC check).
            $table->longText('raw_body')->nullable()->after('payload');

            // Only meaningful when destination = fanscorner.
            $table->string('forward_status')->nullable()->after('status'); // pending, forwarded, failed
            $table->unsignedInteger('forward_attempts')->default(0)->after('forward_status');
            $table->timestamp('forwarded_at')->nullable()->after('forward_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paystack_webhook_logs', function (Blueprint $table) {
            $table->dropColumn([
                'destination',
                'raw_body',
                'forward_status',
                'forward_attempts',
                'forwarded_at',
            ]);
        });
    }
};
