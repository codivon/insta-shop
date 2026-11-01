<?php

use App\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Cron-job heartbeat
|--------------------------------------------------------------------------
|
| Every `php artisan schedule:run` (typically wired to a cPanel Cron Job
| every minute) stamps developer.cron_last_run in the settings table.
| The Developer panel reads this stamp: if it is younger than ~2 minutes
| the cron is reported as ACTIVE, otherwise the panel shows setup
| instructions. Cheap, honest and works on shared hosting.
|
*/
Schedule::call(function () {
    try {
        Setting::set('developer.cron_last_run', now()->format('Y-m-d H:i:s'));
    } catch (\Throwable $e) {
        logger()->warning('cron heartbeat failed: '.$e->getMessage());
    }
})
    ->everyMinute()
    ->name('instashop-cron-heartbeat')
    ->withoutOverlapping();
