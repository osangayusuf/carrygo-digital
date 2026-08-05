<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTermsAccepted
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasAcceptedTerms()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You must accept the Terms and Conditions before continuing.',
                    'terms_required' => true,
                ], 403);
            }

            return back()->withErrors([
                'terms' => 'You must accept the Terms and Conditions before continuing.',
            ]);
        }

        return $next($request);
    }
}
