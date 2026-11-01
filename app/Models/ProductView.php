<?php

namespace App\Models;

use App\Support\Fmt;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Throwable;

#[Fillable(['product_id', 'shop_id', 'day', 'views'])]
class ProductView extends Model
{
    protected function casts(): array
    {
        return [
            'views' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Record one view for today (Asia/Tehran): +1 on products.views and
     * +1 on the (product, day) aggregate row. Race-safe without locks:
     * try the increment first, fall back to insert, ignore the unique
     * collision if a concurrent request created the row meanwhile.
     */
    public static function recordView(Product $product): void
    {
        $product->increment('views');

        $day = now()->toDateString();

        try {
            $updated = static::query()
                ->where('product_id', $product->id)
                ->where('day', $day)
                ->increment('views');

            if (! $updated) {
                static::query()->create([
                    'product_id' => $product->id,
                    'shop_id'    => $product->shop_id,
                    'day'        => $day,
                    'views'      => 1,
                ]);
            }
        } catch (Throwable $e) {
            // Unique (product_id, day) collision or table not migrated yet —
            // the main products.views counter is already bumped, so swallow.
            report($e);
        }
    }

    /** ۱۴۰۵/۰۸/۰۹-style Jalali label for a day row (chart axis). */
    public function dayLabel(): string
    {
        $date = Carbon::parse($this->day);

        return Fmt::fa($date->locale('fa')->dayName.' '.$date->day);
    }
}
