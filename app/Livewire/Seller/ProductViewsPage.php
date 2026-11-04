<?php

namespace App\Livewire\Seller;

use App\Models\Product;
use App\Models\ProductView;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Seller — گزارش کامل بازدید محصولات (full product-views ranking):
 * every product of the shop ranked by views inside a selectable period
 * (همه/امروز/۷روز/۳۰روز/بازه دلخواه), searchable, sortable asc/desc,
 * lazily loaded 12 rows at a time («نمایش بیشتر»).
 */
#[Layout('layouts.seller-slot')]
class ProductViewsPage extends Component
{
    public const PER_PAGE = 12;

    public string $search = '';

    /** desc = پربازدیدترین اول، asc = کم‌بازدیدترین اول */
    public string $sort = 'desc';

    /** all | today | d7 | d30 | custom */
    public string $range = 'd30';

    public string $from = '';

    public string $to = '';

    public int $visible = self::PER_PAGE;

    public function mount(): void
    {
        $today = CarbonImmutable::today();
        $this->to = $today->toDateString();
        $this->from = $today->subDays(29)->toDateString();
    }

    /* any filter change resets the lazy window */
    public function updatedSearch(): void
    {
        $this->visible = self::PER_PAGE;
    }

    public function updatedRange(): void
    {
        $this->visible = self::PER_PAGE;
    }

    public function updatedFrom(): void
    {
        $this->visible = self::PER_PAGE;
    }

    public function updatedTo(): void
    {
        $this->visible = self::PER_PAGE;
    }

    public function toggleSort(): void
    {
        $this->sort = $this->sort === 'desc' ? 'asc' : 'desc';
        $this->visible = self::PER_PAGE;
    }

    /** Lazy loading: reveal the next chunk. */
    public function loadMore(): void
    {
        $this->visible += self::PER_PAGE;
    }

    public function render()
    {
        $seller = auth('seller')->user();
        $shopId = (int) ($seller?->shop?->id ?? 0);

        $bounds = $this->rangeBounds();

        // correlated subquery: views of each product inside the window
        $sumSql = '(SELECT COALESCE(SUM(pv.views), 0) FROM product_views pv '
            .'WHERE pv.product_id = products.id AND pv.shop_id = '.((int) $shopId)
            .($bounds ? ' AND pv.day BETWEEN ? AND ?' : '').')';

        $bindings = $bounds ? [$bounds[0], $bounds[1]] : [];

        $base = Product::query()
            ->where('products.shop_id', $shopId)
            ->select('products.*')
            ->selectRaw($sumSql.' as period_views', $bindings)
            ->when($this->search !== '', fn ($q) => $q->where('products.name', 'like', '%'.$this->search.'%'))
            ->orderBy('period_views', $this->sort === 'asc' ? 'asc' : 'desc')
            ->orderByDesc('products.id');

        $products = (clone $base)
            ->limit($this->visible)
            ->get();

        $totalMatching = (clone $base)->toBase()->count();

        $periodViewsTotal = $shopId === 0 ? 0 : (int) ProductView::query()
            ->where('shop_id', $shopId)
            ->when($bounds, fn ($q) => $q->whereBetween('day', $bounds))
            ->sum('views');

        return view('livewire.seller.product-views-page', [
            'products' => $products,
            'totalMatching' => $totalMatching,
            'periodViewsTotal' => $periodViewsTotal,
            'hasMore' => $totalMatching > $products->count(),
        ])->title('گزارش بازدید محصولات');
    }

    /** [from, to] day strings for the selected range (null = همه بازه‌ها). */
    private function rangeBounds(): ?array
    {
        $today = CarbonImmutable::today();

        return match ($this->range) {
            'today' => [$today->toDateString(), $today->toDateString()],
            'd7' => [$today->subDays(6)->toDateString(), $today->toDateString()],
            'd30' => [$today->subDays(29)->toDateString(), $today->toDateString()],
            'custom' => $this->validCustomRange(),
            default => null,
        };
    }

    private function validCustomRange(): ?array
    {
        try {
            $f = Carbon::parse($this->from)->startOfDay();
            $t = Carbon::parse($this->to)->endOfDay();
        } catch (\Throwable) {
            return null;
        }

        if ($f->gt($t)) {
            [$f, $t] = [$t->copy()->startOfDay(), $f->copy()->endOfDay()];
        }

        return [$f->toDateString(), $t->toDateString()];
    }
}
