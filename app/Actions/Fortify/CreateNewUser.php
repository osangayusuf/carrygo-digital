<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\RewardSource;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(
        protected WalletService $walletService
    ) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],
        ])->validate();

        return DB::transaction(function () use ($input) {
            $referrer = null;
            if (! empty($input['referral_code'])) {
                $referrer = User::where('referral_code', $input['referral_code'])->first();
            }

            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'password' => $input['password'],
                'referred_by' => $referrer?->id,
            ]);

            if ($referrer !== null && config('points.referral.enabled', true)) {
                $refereePoints = (int) config('points.referral.referee_signup_points', 0);
                if ($refereePoints > 0) {
                    $this->walletService->awardBonusPoints($user, $refereePoints, [
                        'source' => RewardSource::ReferralSignup->value,
                        'description' => 'Referral signup bonus for using code '.$referrer->referral_code,
                        'referrer_id' => $referrer->id,
                    ], true);
                }

                $referrerPoints = (int) config('points.referral.referrer_signup_points', 0);
                if ($referrerPoints > 0) {
                    $this->walletService->awardBonusPoints($referrer, $referrerPoints, [
                        'source' => RewardSource::ReferralSignup->value,
                        'description' => 'Referral bonus for inviting '.$user->name,
                        'referred_user_id' => $user->id,
                    ], true);
                }
            }

            return $user;
        });
    }
}
