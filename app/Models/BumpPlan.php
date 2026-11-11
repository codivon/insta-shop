<?php

namespace App\Models;

use App\Support\Fmt;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * One «نردبان» PLAN (v0.24.0) — a sellable duration/price package the
 * ADMIN defines («مثلاً ۱۰ روز با این قیمت») on the dedicated
 * «طرح‌های نردبان» page; the SELLER picks one and pays it from the
 * wallet (BumpService). This replaces the flat per-day rate of v0.23.0
 * — the on/off decision now lives in the «تنظیمات فروشگاه‌ها» toggle
 * (shops.bump_enabled) while ALL pricing lives here.
 */
#[Fillable(['title', 'days', 'price', 'is_active', 'sort'])]
class BumpPlan extends Model
{
    protected function casts(): array
    {
        return [
            'days' => 'integer',
            'price' => 'integer',
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    /** The plan's bumps (ledger for the «فروخته‌شده» counter + delete guard). */
    public function bumps(): HasMany
    {
        return $this->hasMany(ProductBump::class);
    }

    /** Human duration line: «۱۰ روزه». */
    public function daysLabel(): string
    {
        return Fmt::toFa($this->days).' روزه';
    }

    /** Sellable plans for the seller panel — active, shortest first. */
    public static function sellable(): Collection
    {
        return static::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('days')
            ->orderBy('id')
            ->get();
    }
}
