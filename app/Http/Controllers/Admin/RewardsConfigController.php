<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class RewardsConfigController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/RewardsConfig/Index', [
            'config' => [
                'points_per_naira' => (int) config('points.points_per_naira'),
                'bonus_conversion_rate' => (float) config('points.bonus_conversion_rate'),
                'min_bid_increment' => (int) config('points.min_bid_increment'),
                'min_deposit_naira' => (int) config('points.min_deposit_naira'),
                'max_deposit_naira' => (int) config('points.max_deposit_naira'),

                'checkin_base_points' => (int) config('rewards.checkin.base_points'),
                'checkin_milestones' => config('rewards.checkin.milestones'),

                'spin_wheel_daily_grant' => (int) config('rewards.spin_wheel.daily_grant'),
                'spin_wheel_segments' => config('rewards.spin_wheel.segments'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'points_per_naira' => 'required|integer|min:1',
            'bonus_conversion_rate' => 'required|numeric|min:0.1',
            'min_bid_increment' => 'required|integer|min:1',
            'min_deposit_naira' => 'required|integer|min:1',
            'max_deposit_naira' => 'required|integer|gt:min_deposit_naira',

            'checkin_base_points' => 'required|integer|min:0',
            'checkin_milestones' => 'required|array',
            'checkin_milestones.*' => 'required|integer|min:0',

            'spin_wheel_daily_grant' => 'required|integer|min:0',
            'spin_wheel_segments' => 'required|array|min:1',
            'spin_wheel_segments.*.points' => 'required|integer|min:0',
            'spin_wheel_segments.*.probability' => 'required|integer|min:0|max:100',
        ]);

        $segments = $request->input('spin_wheel_segments');
        $totalProbability = collect($segments)->sum('probability');

        if ($totalProbability !== 100) {
            return back()->withErrors([
                'spin_wheel_segments' => __('The spin wheel segment probabilities must sum up to exactly 100% (currently :total%).', ['total' => $totalProbability]),
            ]);
        }

        $configs = [
            'points.points_per_naira' => [$request->input('points_per_naira'), 'integer'],
            'points.bonus_conversion_rate' => [$request->input('bonus_conversion_rate'), 'float'],
            'points.min_bid_increment' => [$request->input('min_bid_increment'), 'integer'],
            'points.min_deposit_naira' => [$request->input('min_deposit_naira'), 'integer'],
            'points.max_deposit_naira' => [$request->input('max_deposit_naira'), 'integer'],

            'rewards.checkin.base_points' => [$request->input('checkin_base_points'), 'integer'],
            'rewards.checkin.milestones' => [$request->input('checkin_milestones'), 'json'],

            'rewards.spin_wheel.daily_grant' => [$request->input('spin_wheel_daily_grant'), 'integer'],
            'rewards.spin_wheel.segments' => [$request->input('spin_wheel_segments'), 'json'],
        ];

        foreach ($configs as $key => [$value, $type]) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $type === 'json' ? json_encode($value) : (string) $value,
                    'type' => $type,
                ]
            );
        }

        // Flush settings cache so they reload from the database on the next request
        Cache::forget('settings.all');

        // Restart queue workers so long-running processes pick up the new config
        Artisan::call('queue:restart');

        return back()->with('success', __('System configurations updated successfully.'));
    }
}
