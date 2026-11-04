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

    /** Story ids whose view counter was already bumped this session. */
    public array $counted = [];

    #[On('open-stories')]
    public function open(int $shopId): void
    {
        if (empty($this->storyIds($shopId))) {
            return;
        }

        $this->shopId = $shopId;
        $this->index = 0;
        $this->counted = [];
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
            $this->dispatch('home-toast', message: 'برای واکنش به استوری، ابتدا وارد حساب خود شوید.');

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
            $this->dispatch('home-toast', message: 'برای ثبت نظر، ابتدا وارد حساب خود شوید.');

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

    /** Bump the view counter once per session + record the «seen» mark. */
    private function markViewed(): void
    {
        $story = $this->current;

        if ($story === null) {
            return;
        }

        if (! in_array($story->id, $this->counted, true)) {
            $story->increment('views');
            $this->counted[] = $story->id;
        }

        $customer = $this->customer();

        if ($customer !== null) {
            StoryView::query()->firstOrCreate([
                'story_id' => $story->id,
                'customer_id' => $customer->id,
            ]);
        }
    }

    private function customer(): ?Customer
    {
        return auth('customer')->user();
    }
}
