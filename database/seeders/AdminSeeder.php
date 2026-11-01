<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

/**
 * The FIRST admin account is now created from the browser at
 * /install/admin-setup (shown right after the web installer finishes,
 * while no admin exists). Keeping this seeder active would defeat that
 * flow, so it only runs when explicitly requested.
 *
 * Local/dev convenience: add  INSTASHOP_SEED_ADMIN=true  to .env to get
 * the legacy dev account below (credentials documented in HANDOFF.md).
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        if (! env('INSTASHOP_SEED_ADMIN', false)) {
            return;
        }

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
