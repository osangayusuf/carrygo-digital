<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate([
                'name' => $role->value,
                'guard_name' => 'web',
            ]);
        }
    }

    public function down(): void
    {
        foreach (UserRole::cases() as $role) {
            Role::where('name', $role->value)
                ->where('guard_name', 'web')
                ->delete();
        }
    }
};
