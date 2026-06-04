<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private readonly ActivityService $activityService) {}

    public function index(Request $request): Response
    {
        $search = $request->filled('search')
            ? trim($request->string('search')->toString())
            : null;

        $roleFilter = $request->filled('role')
            ? $request->string('role')->toString()
            : null;

        $query = User::query()
            ->with('roles')
            ->orderBy('id', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($roleFilter) {
            $query->role($roleFilter);
        }

        $users = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'role' => $roleFilter,
            ],
            'availableRoles' => Role::all()->pluck('name'),
        ]);
    }

    public function toggleActive(User $user, Request $request): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors([
                'error' => __('You cannot deactivate your own administrative account.'),
            ]);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        // If deactivated, force invalidate their sessions
        if (! $user->is_active) {
            // Note: Since they are logged out on their next request by the EnsureUserActive middleware,
            // we don't strictly need to flush their sessions manually here, but toggling is saved.
        }

        return back()->with('success', $user->is_active
            ? __('User account enabled successfully.')
            : __('User account disabled successfully.')
        );
    }

    public function updateRole(User $user, Request $request): RedirectResponse
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $role = $request->string('role')->toString();

        if ($user->id === $request->user()->id && $role !== 'admin') {
            return back()->withErrors([
                'error' => __('You cannot change or demote your own administrative role.'),
            ]);
        }

        $user->syncRoles($role);

        return back()->with('success', __('User role updated successfully.'));
    }
}
