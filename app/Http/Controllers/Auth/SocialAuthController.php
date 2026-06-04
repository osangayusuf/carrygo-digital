<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialProvider;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the provider authentication page.
     */
    public function redirect(string $provider): RedirectResponse
    {
        if (! in_array($provider, ['google', 'facebook'])) {
            return redirect()->route('login')->withErrors([
                'email' => __('Unsupported authentication provider.'),
            ]);
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from the provider.
     */
    public function callback(string $provider): RedirectResponse
    {
        if (! in_array($provider, ['google', 'facebook'])) {
            return redirect()->route('login')->withErrors([
                'email' => __('Unsupported authentication provider.'),
            ]);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Exception $e) {
            Log::error("OAuth Callback Error [{$provider}]: ".$e->getMessage());

            return redirect()->route('login')->withErrors([
                'email' => __('Authentication failed or was cancelled.'),
            ]);
        }

        if (blank($socialUser->getEmail())) {
            return redirect()->route('login')->withErrors([
                'email' => __('Could not retrieve email address from the social account.'),
            ]);
        }

        $user = DB::transaction(function () use ($provider, $socialUser) {
            // 1. Check if the social provider is already linked
            $socialProvider = SocialProvider::where('provider_name', $provider)
                ->where('provider_id', $socialUser->getId())
                ->first();

            if ($socialProvider) {
                // Update tokens
                $socialProvider->update([
                    'token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken,
                    'expires_at' => isset($socialUser->expiresIn) ? now()->addSeconds($socialUser->expiresIn) : null,
                ]);

                return $socialProvider->user;
            }

            // 2. Check if a user with the same email already exists (Option A: Automatic Linking)
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // Link this provider to the existing user
                $user->socialProviders()->create([
                    'provider_name' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken,
                    'expires_at' => isset($socialUser->expiresIn) ? now()->addSeconds($socialUser->expiresIn) : null,
                ]);

                // Proactively verify email if not already verified
                if ($user->email_verified_at === null) {
                    $user->forceFill(['email_verified_at' => now()])->save();
                }

                return $user;
            }

            // 3. Create a brand new user
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                'email' => $socialUser->getEmail(),
                'phone' => null, // Will be filled during onboarding
                'password' => null, // No password for OAuth users
            ]);

            // Set email as verified since OAuth provider verified it
            $user->forceFill(['email_verified_at' => now()])->save();

            // Link the social provider
            $user->socialProviders()->create([
                'provider_name' => $provider,
                'provider_id' => $socialUser->getId(),
                'token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
                'expires_at' => isset($socialUser->expiresIn) ? now()->addSeconds($socialUser->expiresIn) : null,
            ]);

            return $user;
        });

        Auth::login($user);

        // Redirect to onboarding if they haven't completed it (e.g. missing phone number)
        if ($user->onboarding_completed_at === null) {
            return redirect()->route('onboarding');
        }

        return redirect()->route('home');
    }
}
