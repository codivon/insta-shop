<?php

namespace App\Providers;

use App\Models\Setting;
use App\Support\SchemaReady;
use App\Support\Sms\SmsManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SmsManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * Shared-hosting compatibility: many cPanel MySQL servers run with
         * MyISAM as the default engine (1000-byte index limit) or legacy
         * InnoDB without a large prefix (767 bytes). With utf8mb4 an indexed
         * varchar(255) needs 1020 bytes and fails with
         * "SQLSTATE[42000] 1071 Specified key was too long".
         * 191 chars × 4 bytes = 764 bytes → fits every engine.
         */
        Schema::defaultStringLength(191);

        /*
         * Runtime settings that must exist before any request is handled:
         *  - developer.dev_mode       → show real exceptions (app.debug)
         *  - security.session_lifetime→ minutes until auto-logout
         * Guarded so pre-install requests (no settings table) never break.
         */
        try {
            if (SchemaReady::check('settings')) {
                /*
                 * SECURITY: dev_mode (real exceptions on screen) must never
                 * unlock stack traces on a PRODUCTION host — a single
                 * settings-table write would otherwise flip it on. Debug
                 * rendering stays a deliberate .env decision (APP_DEBUG)
                 * plus a local-env convenience.
                 */
                if ((bool) Setting::get('developer.dev_mode') && ! app()->isProduction()) {
                    config(['app.debug' => true]);
                }

                $lifetime = (int) Setting::get('security.session_lifetime', 0);

                if ($lifetime > 0) {
                    config(['session.lifetime' => $lifetime]);
                }

                /*
                 * SECURITY: when the operator forces HTTPS, session cookies
                 * must never travel over plain HTTP either — mark them
                 * Secure before the session middleware builds them.
                 */
                if ((bool) Setting::get('security.force_https')) {
                    config(['session.secure' => true]);
                }
            }
        } catch (\Throwable) {
            // pre-install / DB unavailable — defaults stay as-is
        }
    }
}
