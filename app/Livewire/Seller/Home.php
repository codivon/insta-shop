<?php

namespace App\Livewire\Seller;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopCategory;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.seller-slot')]
class Home extends Component
{
    public function render()
    {
        $seller = auth('seller')->user();
        $shop   = Shop::ensureForSeller($seller);

        $base = Product::query()->where('shop_id', $shop->id);

        $stats = [
            'total'      => (clone $base)->count(),
            'published'  => (clone $base)->where('status', 'published')->count(),
            'outOfStock' => (clone $base)->where('stock', 0)->count(),
            'categories' => ShopCategory::query()->where('shop_id', $shop->id)->count(),
        ];

        return view('livewire.seller.home', [
            'shop'         => $shop,
            'stats'        => $stats,
            'chart'        => $this->lastSevenDays($shop),
            'recentProducts' => (clone $base)->with('mainImage')->latest()->take(4)->get(),
        ])->title('خانه فروشنده');
    }

    /** Product-creation counts for the last 7 days (mini bar chart). */
    private function lastSevenDays(Shop $shop): Collection
    {
        $start = CarbonImmutable::today()->subDays(6);

        $counts = Product::query()
            ->where('shop_id', $shop->id)
            ->whereBetween('created_at', [$start->startOfDay(), CarbonImmutable::today()->endOfDay()])
            ->get()
            ->groupBy(fn (Product $p) => $p->created_at->toDateString())
            ->map->count();

        return collect(range(0, 6))->map(function (int $i) use ($start, $counts) {
            $day = $start->addDays($i);

            return [
                'label' => $day->locale('fa')->dayName,      // شنبه، یکشنبه، ...
                'count' => (int) ($counts[$day->toDateString()] ?? 0),
                'today' => $i === 6,
            ];
        });
    }
}
