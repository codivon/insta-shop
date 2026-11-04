<?php

namespace App\Livewire\Seller;

use App\Models\BankCard;
use App\Models\Carrier;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PushSubscription;
use App\Models\Shop;
use App\Models\Story;
use App\Support\Fmt;
use App\Support\JDate;
use Carbon\CarbonImmutable;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.seller-slot')]
class Home extends Component
{
    /* ── chart controls ── */
    public string $chartMode = 'daily'; // daily | monthly | yearly

    public string $chartFrom = '';

    public string $chartTo = '';

    public function mount(): void
    {
        // default window: last 7 days (paid sales chart)
        $this->chartTo = CarbonImmutable::today()->toDateString();
        $this->chartFrom = CarbonImmutable::today()->subDays(6)->toDateString();
    }

    public function setChartMode(string $mode): void
    {
        $this->chartMode = in_array($mode, ['daily', 'monthly', 'yearly'], true) ? $mode : 'daily';
        // a sensible window per mode
        $today = CarbonImmutable::today();

        $this->chartTo = $today->toDateString();

        $this->chartFrom = match ($this->chartMode) {
            'daily' => $today->subDays(6)->toDateString(),
            'monthly' => $today->subMonths(5)->startOfMonth()->toDateString(),
            default => $today->subYears(3)->startOfYear()->toDateString(),
        };
    }

    /** Public storefront address of this shop (only when the shop is publicly visible). */
    public function getStorefrontUrlProperty(): ?string
    {
        $seller = auth('seller')->user();

        if (! $seller || $seller->status !== 'approved') {
            return null; // pending shops 404 publicly → hide the button
        }

        $shop = Shop::ensureForSeller($seller);
        $handle = $shop->instagram_username ?: $seller->instagram_username;

        return $handle ? route('shop.storefront', ['instagram' => $handle]) : null;
    }

    public function render()
    {
        $seller = auth('seller')->user();
        $shop = Shop::ensureForSeller($seller);

        $base = Product::query()->where('shop_id', $shop->id);

        $stats = [
            'total' => (clone $base)->count(),
            'published' => (clone $base)->where('status', 'published')->count(),
            'outOfStock' => (clone $base)->where('stock', 0)->count(),
        ];

        $chart = $this->salesChart($shop);
        $storyChart = $this->storyChart($shop);

        return view('livewire.seller.home', [
            'shop' => $shop,
            'stats' => $stats,
            'storefrontUrl' => $this->storefrontUrl,
            'chart' => $chart['series'],
            'chartTotal' => $chart['total'],
            'chartOrders' => $chart['orders'],
            'chartMax' => $chart['max'],
            'storyChart' => $storyChart['series'],
            'storyChartMax' => $storyChart['max'],
            'storyChartTotal' => $storyChart['total'],
            'recentProducts' => (clone $base)->with('mainImage')->latest()->take(4)->get(),
            'onboarding' => $this->onboarding($shop),
        ])->title('خانه فروشنده');
    }

    /**
     * Store-setup checklist for the home page: 5 essential steps the seller
     * must complete IN ORDER. Completed steps are dropped from the list;
     * the first unfinished one is the active call-to-action and the rest
     * stay locked. The whole card disappears once everything is done.
     */
    private function onboarding(Shop $shop): array
    {
        $seller = auth('seller')->user();
        $shopId = $shop->id;

        $steps = [
            [
                'key' => 'product',
                'title' => 'افزودن محصول',
                'desc' => 'اولین محصول خود را با عکس و قیمت ثبت کنید.',
                'href' => route('seller.products.create'),
                'icon' => 'package',
                'done' => Product::query()->where('shop_id', $shopId)->exists(),
            ],
            [
                'key' => 'shipping',
                'title' => 'افزودن روش ارسال',
                'desc' => 'روش‌های حمل‌ونقل و هزینه ارسال را تعیین کنید.',
                'href' => route('seller.shipping'),
                'icon' => 'truck',
                'done' => Carrier::query()->where('shop_id', $shopId)->exists(),
            ],
            [
                'key' => 'card',
                'title' => 'افزودن کارت بانکی',
                'desc' => 'کارت بانکی خود را برای تسویه فروش‌ها ثبت کنید.',
                'href' => route('seller.cards'),
                'icon' => 'credit-card',
                'done' => BankCard::query()->where('seller_id', $seller?->id)->exists(),
            ],
            [
                'key' => 'push',
                'title' => 'فعال‌سازی نوتیفیکیشن',
                'desc' => 'برای اطلاع فوری از سفارش‌های جدید، نوتیفیکیشن دستگاه را روشن کنید.',
                'href' => route('seller.notifications'),
                'icon' => 'bell',
                'done' => $seller ? PushSubscription::query()
                    ->where('audience', PushSubscription::AUDIENCE_SELLER)
                    ->where('seller_id', $seller->id)
                    ->exists() : false,
            ],
        ];

        $doneCount = count(array_filter($steps, fn ($s) => $s['done']));

        $remaining = collect($steps)
            ->filter(fn ($s) => ! $s['done'])
            ->values();

        return [
            'steps' => $steps,
            'remaining' => $remaining,
            'doneCount' => $doneCount,
            'total' => count($steps),
            'allDone' => $doneCount === count($steps),
        ];
    }

    /** Views per story (recent 10) for the story-stats chart on the home page. */
    private function storyChart(Shop $shop): array
    {
        $stories = Story::query()
            ->where('shop_id', $shop->id)
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'caption', 'link_title', 'views', 'created_at']);

        return [
            'series' => $stories->map(fn (Story $story) => [
                'value' => (int) $story->views,
                'label' => $story->caption !== null && $story->caption !== ''
                    ? mb_substr($story->caption, 0, 16)
                    : ($story->link_title !== null && $story->link_title !== '' ? mb_substr($story->link_title, 0, 16) : 'استوری #'.Fmt::toFa($story->id)),
                'date' => JDate::date($story->created_at),
            ]),
            'max' => max(1, (int) $stories->max('views')),
            'total' => (int) $stories->sum('views'),
        ];
    }

    /**
     * Paid-sales series for the selected window & granularity.
     * Returns [series (label/value/count/today), total, orders, max].
     */
    private function salesChart(Shop $shop): array
    {
        try {
            $from = CarbonImmutable::parse($this->chartFrom)->startOfDay();
            $to = CarbonImmutable::parse($this->chartTo)->endOfDay();
        } catch (\Throwable) {
            $from = CarbonImmutable::today()->subDays(6)->startOfDay();
            $to = CarbonImmutable::today()->endOfDay();
        }

        if ($from->gt($to)) {
            [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
        }

        // hard caps keep the query + loop light on shared hosting
        $from = match ($this->chartMode) {
            'daily' => $from->max(CarbonImmutable::today()->subDays(91)->startOfDay()),
            'monthly' => $from->max(CarbonImmutable::today()->subYears(3)->startOfDay()),
            default => $from->max(CarbonImmutable::today()->subYears(9)->startOfDay()),
        };

        /* paid order_items of this shop inside the window */
        $rows = OrderItem::query()
            ->selectRaw('order_items.unit_price, order_items.quantity, orders.paid_at, orders.id as order_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.shop_id', $shop->id)
            ->where('orders.status', 'paid')
            ->whereBetween('orders.paid_at', [$from, $to])
            ->get();

        $ordersCount = $rows->pluck('order_id')->unique()->count();
        $total = (int) $rows->sum(fn ($r) => (int) $r->unit_price * (int) $r->quantity);

        /* bucketize */
        $buckets = [];

        if ($this->chartMode === 'daily') {
            $cursor = $from->startOfDay();
            $steps = $from->diffInDays($to->endOfDay()) + 1;
            for ($i = 0; $i < min((int) $steps, 92); $i++) {
                $key = $cursor->toDateString();
                $buckets[$key] = [
                    'label' => $cursor->locale('fa')->dayName.' '.Fmt::fa($cursor->day),
                    'sort' => $key,
                    'today' => $key === CarbonImmutable::today()->toDateString(),
                ];
                $cursor = $cursor->addDay();
            }
        } elseif ($this->chartMode === 'monthly') {
            $buckets = JDate::monthBuckets($from, $to, withYear: false);
        } else {
            $buckets = JDate::yearBuckets($from, $to);
        }

        foreach ($rows as $row) {
            if (! $row->paid_at) {
                continue;
            }

            $paid = CarbonImmutable::parse($row->paid_at);

            $key = match ($this->chartMode) {
                'daily' => $paid->toDateString(),
                'monthly' => JDate::monthKey($paid),
                default => JDate::yearKey($paid),
            };

            if (isset($buckets[$key])) {
                $buckets[$key]['value'] = ($buckets[$key]['value'] ?? 0) + (int) $row->unit_price * (int) $row->quantity;
            }
        }

        $series = collect($buckets)
            ->sortBy('sort')
            ->map(fn ($b) => [
                'label' => $b['label'],
                'value' => (int) ($b['value'] ?? 0),
                'today' => $b['today'],
            ])
            ->values();

        return [
            'series' => $series,
            'total' => $total,
            'orders' => $ordersCount,
            'max' => max($series->max('value'), 1),
        ];
    }
}
