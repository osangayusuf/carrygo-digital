<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('department')->nullable()->after('phone');
            $table->string('employee_id')->nullable()->unique()->after('department');
            $table->timestamp('agent_approved_at')->nullable()->after('employee_id');
            $table->timestamp('agent_rejected_at')->nullable()->after('agent_approved_at');
            $table->foreignId('approved_by')->nullable()->after('agent_rejected_at')
                ->constrained('users', indexName: 'fk_users_approved_by')
                ->nullOnDelete();

            $table->index('agent_approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('fk_users_approved_by');
            $table->dropIndex('users_agent_approved_at_index');
            $table->dropColumn(['department', 'employee_id', 'agent_approved_at', 'agent_rejected_at', 'approved_by']);
        });
    }
};
