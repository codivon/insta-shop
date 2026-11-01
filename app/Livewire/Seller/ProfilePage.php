<?php

namespace App\Livewire\Seller;

use App\Models\Wallet;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.seller-slot')]
class ProfilePage extends Component
{
    public string $full_name = '';

    public function mount(): void
    {
        $this->full_name = (string) ($this->seller()?->full_name ?? '');
    }

    protected function rules(): array
    {
        return [
            'full_name' => ['nullable', 'string', 'min:3', 'max:60'],
        ];
    }

    protected function messages(): array
    {
        return [
            'full_name.min' => 'نام باید حداقل ۳ حرف باشد.',
            'full_name.max' => 'نام حداکثر ۶۰ حرف است.',
        ];
    }

    public function saveName(): void
    {
        $this->validate();

        $this->seller()?->update([
            'full_name' => trim($this->full_name) !== '' ? trim($this->full_name) : null,
        ]);

        $this->dispatch('seller-profile-toast', message: 'نام شما ذخیره شد. ✅');
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getStatsProperty(): array
    {
        $seller = $this->seller();
        $shop   = $seller?->shop;
        $shopId = $shop?->id ?? 0;

        return [
            'products' => $shop ? $shop->products()->count() : 0,
            'orders'   => $shopId > 0 ? \App\Models\Order::query()
                ->whereHas('items', fn ($q) => $q->where('shop_id', $shopId))
                ->count() : 0,
            'balance'  => $seller ? (int) Wallet::ensureForSeller($seller->id)->balance : 0,
            'views'    => $shop ? (int) $shop->products()->sum('views') : 0,
            'cards'    => $seller ? \App\Models\BankCard::query()->where('seller_id', $seller->id)->count() : 0,
        ];
    }

    public function getJoinedAtProperty(): string
    {
        $seller = $this->seller();

        if (! $seller?->created_at) {
            return '';
        }

        return \App\Support\JDate::date($seller->created_at);
    }

    public function render()
    {
        return view('livewire.seller.profile-page', [
            'seller'   => $this->seller(),
            'shop'     => $this->seller()?->shop,
            'stats'    => $this->stats,
            'joinedAt' => $this->joinedAt,
        ])->title('پروفایل');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function seller()
    {
        return auth('seller')->user();
    }
}
