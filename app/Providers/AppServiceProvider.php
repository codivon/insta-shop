<?php

namespace App\Providers;

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
    }
}
