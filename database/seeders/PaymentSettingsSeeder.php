<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class PaymentSettingsSeeder extends Seeder
{
    /**
     * Seed the payment gateway setting (idempotent — never overrides an
     * admin's existing choice).
     */
    public function run(): void
    {
        if (Setting::get('payment.gateway') === null) {
            Setting::set('payment.gateway', 'local');
        }
    }
}
