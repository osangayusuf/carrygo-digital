<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * @param  Request  $request
     */
    public function toResponse($request)
    {
        $redirect = Features::enabled(Features::emailVerification())
            ? route('verification.notice')
            : Fortify::redirects('register');

        return $request->wantsJson()
            ? new JsonResponse('', 201)
            : redirect()->intended($redirect);
    }
}
