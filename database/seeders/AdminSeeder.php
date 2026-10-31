<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

/**
 * First admin account (dev credentials are documented in HANDOFF.md).
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'admin@caffeinet.ir'],
            [
                'name' => 'مدیر InstaShop',
                'password' => 'Adm!n-Sh0p-1405',
                'is_super' => true,
            ],
        );
    }
}
