<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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
            if (Schema::hasTable('settings')) {
                $settings = Cache::rememberForever('settings.all', function () {
                    return Setting::all()
                        ->mapWithKeys(fn (Setting $setting) => [$setting->key => $setting->castValue()])
                        ->all();
                });

                foreach ($settings as $key => $value) {
                    config([$key => $value]);
                }

                Log::info('SettingsServiceProvider loaded DB settings into config.', [
                    'settings_count' => count($settings),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('SettingsServiceProvider failed to load DB settings into config.', [
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
