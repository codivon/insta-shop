<?php

namespace App\Livewire\Seller\Notifications;

use App\Models\SellerNotification;
use Livewire\Component;

/**
 * Seller — tiny UNREAD badge for the bottom tab bar («اعلان‌ها»).
 * Polls every 60s so a fresh order/wallet event lights the tab up
 * without a navigation.
 */
class UnreadBadge extends Component
{
    public function getCountProperty(): int
    {
        $sellerId = (int) (auth('seller')->id() ?? 0);

        if ($sellerId <= 0) {
            return 0;
        }

        return SellerNotification::query()->where('seller_id', $sellerId)->unread()->count();
    }

    public function render()
    {
        return view('livewire.seller.notifications.unread-badge');
    }
}
