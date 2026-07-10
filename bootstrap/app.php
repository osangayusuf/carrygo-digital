<?php

use App\Http\Middleware\EnsureAgentApproved;
use App\Http\Middleware\EnsureUserActive;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\TrackAgentActivity;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('support/*') || $request->is('support') || $request->routeIs('support.*')) {
                return route('support.login');
            }

            if ($request->routeIs('winners') || $request->routeIs('leaderboard') || $request->routeIs('recommended')) {
                return route('register', ['redirected' => 1]);
            }

            return route('login');
        });

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            EnsureUserActive::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'agent.approved' => EnsureAgentApproved::class,
            'agent.activity' => TrackAgentActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (UnauthorizedException $e, Request $request) {
            $user = $request->user();
            if ($user) {
                if ($user->hasRole('admin')) {
                    return redirect()->route('admin.dashboard');
                }
                if ($user->isAgent()) {
                    return redirect()->route('support.dashboard');
                }
            }

            return redirect()->route('home');
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            $user = $request->user();
            if ($user) {
                if ($user->hasRole('admin')) {
                    return redirect()->route('admin.dashboard');
                }
                if ($user->isAgent()) {
                    return redirect()->route('support.dashboard');
                }
            }

            return redirect()->route('home');
        });
    })->create();
