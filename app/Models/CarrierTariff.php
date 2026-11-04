<?php

namespace App\Models;

use App\Support\Fmt;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['carrier_id', 'zone_type', 'max_weight', 'cost', 'sort'])]
class CarrierTariff extends Model
{
    /** درون استانی — مقصد در همان استان مبدأ حامل. */
    public const ZONE_IN = 'in_province';

    /** بیرون استانی — سایر استان‌ها. */
    public const ZONE_OUT = 'out_province';

    public const ZONES = [
        self::ZONE_IN => 'درون استانی',
        self::ZONE_OUT => 'بیرون استانی',
    ];

    protected function casts(): array
    {
        return [
            'max_weight' => 'integer',
            'cost' => 'integer',
            'sort' => 'integer',
        ];
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function scopeForZone(Builder $query, string $zone): Builder
    {
        return $query->where('zone_type', $zone)->orderBy('max_weight')->orderBy('sort')->orderBy('id');
    }

    public function zoneLabel(): string
    {
        return self::ZONES[$this->zone_type] ?? $this->zone_type;
    }

    /** «تا ۱ کیلوگرم» / «تا ۵۰۰ گرم». */
    public function weightLabel(): string
    {
        return $this->max_weight >= 1000
            ? 'تا '.Fmt::toFa(rtrim(rtrim(number_format($this->max_weight / 1000, 1, '.', ''), '0'), '.')).' کیلوگرم'
            : 'تا '.Fmt::toFa($this->max_weight).' گرم';
    }
}
