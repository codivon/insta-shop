<?php

namespace App\Livewire\Seller;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\BankCard;
use App\Models\Order;
use App\Models\Wallet;
use App\Support\JDate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.seller-slot')]
class ProfilePage extends Component
{
    use NotifiesOnValidation;

    // ── Computed data ────────────────────────────────────────────────

    public function getStatsProperty(): array
    {
        $seller = $this->seller();
        $shop = $seller?->shop;
        $shopId = $shop?->id ?? 0;

        return [
            'products' => $shop ? $shop->products()->count() : 0,
            'orders' => $shopId > 0 ? Order::query()
                ->whereHas('items', fn ($q) => $q->where('shop_id', $shopId))
                ->count() : 0,
            'balance' => $seller ? (int) Wallet::ensureForSeller($seller->id)->balance : 0,
            'views' => $shop ? (int) $shop->products()->sum('views') : 0,
            'cards' => $seller ? BankCard::query()->where('seller_id', $seller->id)->count() : 0,
        ];
    }

    public function getJoinedAtProperty(): string
    {
        $seller = $this->seller();

        if (! $seller?->created_at) {
            return '';
        }

        return JDate::date($seller->created_at);
    }

    public function render()
    {
        return view('livewire.seller.profile-page', [
            'seller' => $this->seller(),
            'shop' => $this->seller()?->shop,
            'stats' => $this->stats,
            'joinedAt' => $this->joinedAt,
        ])->title('پروفایل');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function seller()
    {
        return auth('seller')->user();
    }
}
