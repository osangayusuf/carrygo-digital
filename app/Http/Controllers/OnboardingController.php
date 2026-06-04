<?php

namespace App\Http\Controllers;

use App\Services\OnboardingPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function __construct(
        private readonly OnboardingPageService $onboardingPageService,
    ) {}

    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->onboarding_completed_at !== null) {
            return redirect()->route('home');
        }

        return Inertia::render('Onboarding/Index', $this->onboardingPageService->buildProps($user));
    }

    public function complete(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->phone === null) {
            $data = $request->validate([
                'phone' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('users', 'phone')->ignore($user->id),
                ],
            ]);

            $user->phone = $data['phone'];
        }

        if ($user->onboarding_completed_at === null) {
            $user->forceFill(['onboarding_completed_at' => now()])->save();
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('You\'re all set — start bidding!'),
        ]);

        return redirect()->route('home');
    }
}
