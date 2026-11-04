<?php

namespace App\Livewire\Seller;

use App\Models\Shop;
use App\Models\Story;
use App\Models\StoryView;
use App\Support\Fmt;
use App\Support\JDate;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Seller — «بازدید استوری‌ها» (story-views analytics, separate page inside
 * the stats panel): summary tiles (views / likes / comments / active) +
 * a CSS bar chart of views per story + the full story list. The chart data
 * covers the 14 most recent stories of the shop.
 */
#[Layout('layouts.seller-slot')]
class StoryViewsPage extends Component
{
    public function render()
    {
        $shop = Shop::ensureForSeller(auth('seller')->user());

        $stories = Story::query()
            ->where('shop_id', $shop->id)
            ->with(['product:id,name'])
            ->withCount(['likes', 'comments'])
            ->orderByDesc('id')
            ->limit(60)
            ->get();

        $totals = [
            'views' => (int) $stories->sum('views'),
            'likes' => (int) $stories->sum('likes_count'),
            'comments' => (int) $stories->sum('comments_count'),
            'active' => $stories->filter(fn (Story $s) => $s->stateLabel() === 'فعال')->count(),
        ];

        $chart = $this->chart($stories);

        return view('livewire.seller.story-views-page', [
            'stories' => $stories,
            'totals' => $totals,
            'chart' => $chart['series'],
            'chartMax' => $chart['max'],
            'uniqueViewers' => $this->uniqueViewers($shop),
        ])->title('بازدید استوری‌ها');
    }

    /** Views per story for the chart (recent 14). */
    private function chart(Collection $stories): array
    {
        $rows = $stories->take(14)->values();

        return [
            'series' => $rows->map(fn (Story $story) => [
                'id' => $story->id,
                'label' => $story->caption !== null && $story->caption !== ''
                    ? mb_substr($story->caption, 0, 18)
                    : ($story->link_title !== null && $story->link_title !== '' ? mb_substr($story->link_title, 0, 18) : 'استوری #'.Fmt::toFa($story->id)),
                'value' => (int) $story->views,
                'date' => JDate::date($story->created_at),
            ]),
            'max' => max(1, (int) $rows->max('views')),
        ];
    }

    /** Unique logged-in viewers across all of this shop's stories. */
    private function uniqueViewers(Shop $shop): int
    {
        return (int) StoryView::query()
            ->whereHas('story', fn ($q) => $q->where('shop_id', $shop->id))
            ->count();
    }
}
