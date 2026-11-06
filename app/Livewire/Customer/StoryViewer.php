<?php

namespace App\Livewire\Customer;

use App\Models\Customer;
use App\Models\Story;
use App\Models\StoryComment;
use App\Models\StoryLike;
use App\Models\StoryView;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Customer — full-screen Instagram-style story viewer: segmented progress
 * bars (5s per story, hold to pause), tap right/left to advance, swipe
 * down to close, shop meta header, caption, attached PRODUCT card
 * (image + price + buy link) and an optional external LINK — plus likes
 * and comments for logged-in customers (guests get a login hint toast).
 * Rendered as a nested component on the home page; opens via the
 * «open-stories» window event fired by the story rings.
 */
class StoryViewer extends Component
{
    public bool $isOpen = false;

    public ?int $shopId = null;

    public int $index = 0;

    public string $commentBody = '';

    #[On('open-stories')]
    public function open(int $shopId, int $startIndex = 0): void
    {
        $ids = $this->storyIds($shopId);

        if ($ids === []) {
            return;
        }

        $this->shopId = $shopId;
        // per-story rings (storefront) open the viewer at the tapped story
        $this->index = min(max(0, $startIndex), count($ids) - 1);
        $this->commentBody = '';
        $this->isOpen = true;

        $this->markViewed();
    }

    public function next(): void
    {
        if (! $this->isOpen) {
            return;
        }

        if ($this->index + 1 >= count($this->storyIds((int) $this->shopId))) {
            $this->close();

            return;
        }

        $this->index++;
        $this->markViewed();
    }

    public function prev(): void
    {
        if (! $this->isOpen || $this->index === 0) {
            return;
        }

        $this->index--;
        $this->markViewed();
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->shopId = null;
        $this->index = 0;
        $this->commentBody = '';
    }

    /** Heart toggle — customers only. */
    public function toggleLike(): void
    {
        $customer = $this->customer();
        $story = $this->current;

        if ($customer === null || $story === null) {
            // generic name — the viewer also runs on the public storefront,
            // where there is no «home-toast» listener
            $this->dispatch('story-toast', message: 'برای واکنش به استوری، ابتدا وارد حساب خود شوید.');

            return;
        }

        // The seller/admin may disable likes per story — respect it server-side.
        if (! $story->likes_enabled) {
            return;
        }

        $existing = StoryLike::query()
            ->where('story_id', $story->id)
            ->where('customer_id', $customer->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return;
        }

        StoryLike::query()->create([
            'story_id' => $story->id,
            'customer_id' => $customer->id,
        ]);
    }

    public function addComment(): void
    {
        $customer = $this->customer();
        $story = $this->current;

        if ($customer === null || $story === null) {
            $this->dispatch('story-toast', message: 'برای ثبت نظر، ابتدا وارد حساب خود شوید.');

            return;
        }

        // The seller/admin may disable comments per story — respect it server-side.
        if (! $story->comments_enabled) {
            return;
        }

        $this->validate([
            'commentBody' => ['required', 'string', 'min:1', 'max:300'],
        ], [
            'commentBody.required' => 'متن نظر را بنویسید.',
            'commentBody.max' => 'نظر حداکثر ۳۰۰ کاراکتر است.',
        ]);

        StoryComment::query()->create([
            'story_id' => $story->id,
            'customer_id' => $customer->id,
            'body' => trim($this->commentBody),
        ]);

        $this->commentBody = '';
    }

    // ── computed data ────────────────────────────────────────────────

    /** Visible stories of the open shop, oldest → newest. */
    public function getStoriesProperty(): Collection
    {
        if ($this->shopId === null) {
            return collect();
        }

        return Story::query()
            ->visible()
            ->ofPublicShops()
            ->where('shop_id', $this->shopId)
            ->with(['shop:id,name,logo_path,instagram_username', 'product.mainImage'])
            ->withCount('likes')
            ->orderBy('id')
            ->get();
    }

    public function getCurrentProperty(): ?Story
    {
        return $this->stories->get($this->index);
    }

    public function getLikedProperty(): bool
    {
        $customer = $this->customer();
        $story = $this->current;

        if ($customer === null || $story === null) {
            return false;
        }

        return StoryLike::query()
            ->where('story_id', $story->id)
            ->where('customer_id', $customer->id)
            ->exists();
    }

    /** Current story comments, oldest → newest (IG appends at the bottom). */
    public function getCommentsProperty(): Collection
    {
        $story = $this->current;

        if ($story === null) {
            return collect();
        }

        return $story->comments()
            ->with('customer:id,full_name')
            ->limit(30)
            ->get()
            ->reverse()
            ->values();
    }

    public function render()
    {
        return view('livewire.customer.story-viewer', [
            'stories' => $this->stories,
            'current' => $this->current,
            'liked' => $this->liked,
            'comments' => $this->comments,
        ]);
    }

    // ── helpers ──────────────────────────────────────────────────────

    private function storyIds(int $shopId): array
    {
        return Story::query()
            ->visible()
            ->ofPublicShops()
            ->where('shop_id', $shopId)
            ->pluck('id')
            ->all();
    }

    /**
     * Bump the view counter + record the «seen» mark.
     * Logged-in customers are keyed by customer_id (drives the ring
     * seen-state too); guests get an anonymous visitor token so the
     * analytics cover every viewer, not only logged-in ones.
     *
     * SECURITY (v0.20.0): the counter dedupe moved server-side. The old
     * gate (`in_array($story->id, $this->counted)`) trusted a client-writable
     * public property — a tampered commit resetting $counted could inflate
     * view counts without bound (they feed seller analytics). The view row
     * (customer_id / visitor_token) is now the source of truth: the counter
     * increments only when a NEW story_views row is minted.
     */
    private function markViewed(): void
    {
        $story = $this->current;

        if ($story === null) {
            return;
        }

        $customer = $this->customer();

        if ($customer !== null) {
            $fresh = StoryView::query()->firstOrCreate([
                'story_id' => $story->id,
                'customer_id' => $customer->id,
            ])->wasRecentlyCreated;

            if ($fresh) {
                $story->increment('views');
            }

            $this->announceSeen($story);

            return;
        }

        $token = $this->visitorToken();

        if ($token !== null) {
            $fresh = StoryView::query()->firstOrCreate([
                'story_id' => $story->id,
                'visitor_token' => $token,
            ])->wasRecentlyCreated;

            if ($fresh) {
                $story->increment('views');
            }

            $this->announceSeen($story);
        }
    }

    /**
     * Live ring grey-out (client v0.17.8: «وقتی استوری مشاهده میشود، border
     * دورش لحظه‌ای از بین نمیرود و باید رفرش شود») — the hosting page's
     * rings read story_views through computed properties, so re-rendering
     * the parent the moment a story is watched turns its ring grey without
     * any manual reload (storefront per-story rings + home per-shop rings).
     */
    private function announceSeen(Story $story): void
    {
        $this->dispatch('story-marked-seen', storyId: $story->id, shopId: (int) $this->shopId);
    }

    /** Stable anonymous id for guest viewers (persisted in the session). */
    private function visitorToken(): ?string
    {
        $session = session();

        if (! $session->isStarted()) {
            return null;
        }

        if (! $session->has('story_visitor_token')) {
            $session->put('story_visitor_token', 'v_'.bin2hex(random_bytes(16)));
        }

        return (string) $session->get('story_visitor_token');
    }

    private function customer(): ?Customer
    {
        return auth('customer')->user();
    }
}
