<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Default SMS settings (group "sms").
 * The "log" driver is active by default so OTP/login works end-to-end
 * without a paid gateway. Admin can switch the provider later.
 */
class SmsSettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('sms.driver', 'log', 'text');
    }
}
