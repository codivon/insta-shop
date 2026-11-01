<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database (idempotent — safe to re-run).
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            SmsSettingsSeeder::class,
            PaymentSettingsSeeder::class,
            SmsTemplateSeeder::class,
        ]);
    }
}
