<?php

namespace App\Livewire\Seller;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Discount;
use App\Models\Shop;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * «کدهای تخفیف» — the shop's discount codes list (reached from the
 * seller profile AND the my-shop page). Create/edit happens in the
 * full-page DiscountForm.
 */
#[Layout('layouts.seller-slot')]
class DiscountIndex extends Component
{
    public function deleteDiscount(int $id): void
    {
        $discount = $this->findDiscount($id);

        if (! $discount) {
            return;
        }

        $discount->delete();

        $this->dispatch('panel-toast', message: 'کد تخفیف حذف شد.', type: 'success');
    }

    public function togglePublished(int $id): void
    {
        $discount = $this->findDiscount($id);

        if ($discount) {
            $discount->update(['published' => ! $discount->published]);
        }
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getDiscountsProperty(): Collection
    {
        return Discount::query()
            ->forShop($this->shop()->id)
            ->withCount([])
            ->get();
    }

    public function getStatsProperty(): array
    {
        $rows = $this->discounts;

        return [
            'total'     => $rows->count(),
            'published' => $rows->where('published', true)->count(),
            'used'      => (int) $rows->sum('used_count'),
        ];
    }

    public function render()
    {
        return view('livewire.seller.discount-index', [
            'discounts' => $this->discounts,
            'stats'     => $this->stats,
        ])->title('کدهای تخفیف');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    protected function shop(): Shop
    {
        return once(fn () => Shop::ensureForSeller(auth('seller')->user()));
    }

    private function findDiscount(int $id): ?Discount
    {
        return Discount::query()->where('shop_id', $this->shop()->id)->find($id);
    }
}
