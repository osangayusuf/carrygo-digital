<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Features;

class LoginResponse implements LoginResponseContract
{
    /**
     * @param  Request  $request
     */
    public function toResponse($request)
    {
        $user = $request->user();

        if (
            $user
            && Features::enabled(Features::emailVerification())
            && ! $user->hasVerifiedEmail()
        ) {
            return $request->wantsJson()
                ? new JsonResponse(['two_factor' => false], 200)
                : redirect()->intended(route('verification.notice'));
        }

        if ($user && $user->hasRole('admin')) {
            return $request->wantsJson()
                ? new JsonResponse(['two_factor' => false, 'intended' => url('/admin/auctions')], 200)
                : redirect()->intended(url('/admin/auctions'));
        }

        if ($user && $user->isAgent()) {
            return $request->wantsJson()
                ? new JsonResponse(['two_factor' => false, 'intended' => url('/support')], 200)
                : redirect()->intended(url('/support'));
        }

        return $request->wantsJson()
            ? new JsonResponse(['two_factor' => false, 'intended' => url('/')], 200)
            : redirect()->intended(url('/'));
    }
}
