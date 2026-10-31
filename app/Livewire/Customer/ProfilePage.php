<?php

namespace App\Livewire\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\Date;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer-slot')]
class ProfilePage extends Component
{
    public string $full_name = '';

    public function mount(): void
    {
        $this->full_name = (string) ($this->customer()?->full_name ?? '');
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

        $this->customer()?->update([
            'full_name' => trim($this->full_name) !== '' ? trim($this->full_name) : null,
        ]);

        $this->dispatch('profile-toast', message: 'نام شما ذخیره شد. ✅');
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getStatsProperty(): array
    {
        $customer = $this->customer();

        return [
            'favorites' => $customer ? $customer->favorites()->count() : 0,
            'cart'      => $customer && $customer->cart ? $customer->cart->items()->count() : 0,
        ];
    }

    public function getJoinedAtProperty(): string
    {
        $customer = $this->customer();

        if (! $customer?->created_at) {
            return '';
        }

        return \App\Support\Fmt::toFa(
            $customer->created_at->locale('fa')->translatedFormat('Y/m/d'),
        );
    }

    public function render()
    {
        return view('livewire.customer.profile-page', [
            'customer' => $this->customer(),
            'stats'    => $this->stats,
            'joinedAt' => $this->joinedAt,
        ])->title('پروفایل');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function customer(): ?Customer
    {
        return auth('customer')->user();
    }
}
