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
    } catch (Throwable $e) {
        logger()->warning('cron heartbeat failed: '.$e->getMessage());
    }
})
    ->everyMinute()
    ->name('instashop-cron-heartbeat')
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Queue worker tick (shared hosting friendly)
|--------------------------------------------------------------------------
|
| Drains the `database` queue and exits — backup / restore jobs are
| pushed there so web requests never time out. The Backups page also
| tries to start a worker instantly (best effort); this tick guarantees
| the job runs within a minute even when that is impossible (php-fpm).
|
*/
Schedule::command('queue:work database --stop-when-empty --max-time=240 --sleep=1 --tries=1')
    ->everyMinute()
    ->name('instashop-queue-worker')
    ->withoutOverlapping(10);

/*
|--------------------------------------------------------------------------
| Automatic backups (تنظیمات → پشتیبان‌گیری خودکار)
|--------------------------------------------------------------------------
|
| `backups:auto` checks (every 10 min) whether the admin-configured
| schedule is due — روزانه / هفتگی / ماهانه / بازهٔ دلخواه + ساعت اجرا —
| and when it is, dumps the full database, notifies the admins and
| optionally emails the archive (split into parts when large).
| `backups:cleanup` deletes auto backups older than the retention window.
|
*/
Schedule::command('backups:auto')
    ->everyTenMinutes()
    ->name('instashop-backups-auto')
    ->withoutOverlapping(10);

Schedule::command('backups:cleanup')
    ->dailyAt('03:30')
    ->name('instashop-backups-cleanup')
    ->withoutOverlapping(30);
