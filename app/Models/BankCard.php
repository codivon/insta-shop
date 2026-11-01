<?php

namespace App\Models;

use App\Support\Banking;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['seller_id', 'full_name', 'card_number', 'sheba', 'is_default'])]
class BankCard extends Model
{
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /* ─────────────────────────── helpers ─────────────────────────── */

    /** Iranian bank name detected from the card BIN. */
    public function bankName(): string
    {
        return Banking::bankName($this->card_number);
    }

    /** «6037 6912 3456 1234». */
    public function formattedCard(): string
    {
        return Banking::formatCard($this->card_number);
    }

    /** «6037 •••• •••• 1234». */
    public function maskedCard(): string
    {
        return Banking::maskCard($this->card_number);
    }

    /** «IR06 2980 0000 0012 3456 7890 01». */
    public function formattedSheba(): string
    {
        return Banking::formatSheba($this->sheba);
    }

    /** Gradients per known bank for the card visual (fallback = neutral). */
    public function cardTheme(): string
    {
        return match ($this->bankName()) {
            'بانک ملت', 'بانک صنعت و معدن'      => 'from-rose-600 via-rose-500 to-rose-400',
            'بانک سامان', 'بانک سرمد (سامان)'   => 'from-sky-700 via-sky-600 to-sky-500',
            'بانک تجارت', 'بانک خاورمیانه (مید)' => 'from-orange-600 via-orange-500 to-amber-400',
            'بانک صادرات', 'بانک پاسارگاد'      => 'from-yellow-600 via-amber-500 to-yellow-400',
            'بانک کشاورزی', 'بانک مهر ایران'    => 'from-lime-700 via-green-600 to-emerald-500',
            'پست بانک'                          => 'from-green-700 via-emerald-600 to-green-500',
            'بانک سپه'                          => 'from-stone-700 via-stone-600 to-stone-500',
            'بانک مسکن'                          => 'from-rose-700 via-pink-600 to-rose-500',
            'بانک کارآفرین', 'بانک اقتصاد نوین'  => 'from-cyan-700 via-cyan-600 to-teal-500',
            default                              => 'from-primary-700 via-primary-600 to-primary-500',
        };
    }
}
