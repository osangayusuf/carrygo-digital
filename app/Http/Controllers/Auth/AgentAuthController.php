<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\CreateNewAgent;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AgentAuthController extends Controller
{
    /**
     * Show the agent login view.
     */
    public function showLogin(): InertiaResponse|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isAgent() || $user->hasRole('admin')) {
                return redirect()->route('support.dashboard');
            }

            return redirect()->route('home');
        }

        return Inertia::render('Support/auth/Login', [
            'status' => session('status'),
        ]);
    }

    /**
     * Show the agent registration view.
     */
    public function showRegister(): InertiaResponse|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isAgent() || $user->hasRole('admin')) {
                return redirect()->route('support.dashboard');
            }

            return redirect()->route('home');
        }

        return Inertia::render('Support/auth/Register');
    }

    /**
     * Handle an incoming agent registration request.
     */
    public function register(Request $request, CreateNewAgent $creator): RedirectResponse
    {
        $user = $creator->create($request->all());

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }

    /**
     * Show the holding pending approval view.
     */
    public function pending(): InertiaResponse
    {
        return Inertia::render('Support/auth/Pending');
    }
}
