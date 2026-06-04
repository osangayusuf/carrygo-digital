<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewAgent implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered agent.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'department' => ['required', 'string', 'max:100'],
            'employee_id' => ['required', 'string', 'max:50', Rule::unique(User::class, 'employee_id')],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input): User {
            $agent = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'department' => $input['department'],
                'employee_id' => $input['employee_id'],
                'password' => $input['password'],
                // agent_approved_at intentionally null — pending admin approval
            ]);

            $agent->assignRole(UserRole::AGENT->value);

            return $agent;
        });
    }
}
