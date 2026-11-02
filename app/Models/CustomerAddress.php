<?php

namespace App\Models;

use App\Support\Fmt;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['customer_id', 'title', 'province_id', 'city_id', 'province_name', 'city_name', 'address', 'postal_code', 'plaque', 'unit', 'recipient_name', 'phone', 'is_default'])]
class CustomerAddress extends Model
{
    protected function casts(): array
    {
        return [
            'province_id' => 'integer',
            'city_id'     => 'integer',
            'is_default'  => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId)->latest('is_default')->orderByDesc('id');
    }

    /** One-line summary for chips/lists: «خانه — تهران، خیابان … پلاک ۱۲». */
    public function summary(): string
    {
        $parts = array_filter([
            $this->province_name,
            $this->city_name,
            mb_substr((string) $this->address, 0, 60),
            $this->plaque !== null && $this->plaque !== '' ? 'پلاک '.Fmt::toFa($this->plaque) : null,
        ]);

        return implode('، ', $parts);
    }

    /**
     * Full multi-line label used at checkout:
     * گیرنده · تلفن · استال/شهر · نشانی + پلاک/واحد · کد پستی.
     */
    public function fullLabel(): string
    {
        $place = trim((string) ($this->province_name ?: '').' '.(string) ($this->city_name ?: ''));

        $line = trim(($this->city_name ?: '').'، '.$this->address);

        if ($this->plaque !== null && $this->plaque !== '') {
            $line .= '، پلاک '.Fmt::toFa($this->plaque);
        }

        if ($this->unit !== null && $this->unit !== '') {
            $line .= '، واحد '.Fmt::toFa($this->unit);
        }

        if ($place !== '' && $this->province_name && $this->city_name && $this->province_name !== $this->city_name) {
            $line = $this->province_name.'، '.$line;
        }

        return $line;
    }
}
