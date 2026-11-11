<?php

namespace Database\Seeders;

use App\Models\BumpPlan;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Starter bump plans (v0.24.0) — «طرح‌های نردبان»: duration+price
 * packages the client edits freely from the admin page (e.g. «۱۰ روزه
 * با این قیمت»). Seeded ONLY when the table is empty, so the client's
 * own pricing is never overwritten by a re-seed.
 *
 * Continuity: a v0.23.0 install that had set the flat per-day rate gets
 * its price converted into the four packages (rate × days); a fresh
 * install gets a sane market-style ladder.
 */
class BumpPlanSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (BumpPlan::query()->exists()) {
            return; // the client already owns their pricing
        }

        // v0.23.0's flat rate → the same economics, now as named packages
        $perDay = (int) Setting::get('shops.bump_price_per_day', 0);
        $rate = $perDay > 0 ? $perDay : 15000;

        $defaults = [
            ['title' => 'نردبان ۳ روزه', 'days' => 3, 'price' => $rate * 3, 'sort' => 1],
            ['title' => 'نردبان ۷ روزه', 'days' => 7, 'price' => $rate * 7, 'sort' => 2],
            ['title' => 'نردبان ۱۴ روزه', 'days' => 14, 'price' => $rate * 14, 'sort' => 3],
            ['title' => 'نردبان ۳۰ روزه', 'days' => 30, 'price' => $rate * 30, 'sort' => 4],
        ];

        foreach ($defaults as $plan) {
            BumpPlan::query()->create($plan + ['is_active' => true]);
        }
    }
}
