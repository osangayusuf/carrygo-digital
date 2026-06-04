<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            // Prevent running if settings table does not exist or database is unavailable
            if (Schema::hasTable('settings')) {
                $settings = Cache::rememberForever('settings.all', function () {
                    return Setting::all();
                });

                foreach ($settings as $setting) {
                    config([$setting->key => $setting->castValue()]);
                }
            }
        } catch (\Exception $e) {
            // Silence exceptions to avoid blocking initial database migrations/setups
        }
    }
}
