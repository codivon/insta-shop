<?php

namespace App\Livewire\Seller\Notifications;

use App\Models\SellerNotification;
use App\Support\WebPusher;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Seller — «نوتیفیکیشن‌ها» page: the seller's OWN notification feed
 * (orders with their products, wallet movements, product/shop status…)
 * with an all/unread filter, mark-read actions and one-click Web Push
 * registration for this device (phone/desktop, works when closed).
 */
#[Layout('layouts.seller-slot')]
#[Title('نوتیفیکیشن‌ها')]
class NotificationsPage extends Component
{
    use WithPagination;

    /** all | unread */
    public string $filter = 'all';

    /** Browser push permission state: default|granted|denied|unsupported|subscribed */
    public string $pushState = 'default';

    public string $vapidKey = '';

    public function mount(): void
    {
        $this->vapidKey = WebPusher::publicKey();

        $this->pushState = $this->vapidKey === '' ? 'unsupported' : 'default';
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    protected function sellerId(): int
    {
        return (int) (auth('seller')->id() ?? 0);
    }

    public function markRead(int $id): void
    {
        SellerNotification::query()
            ->where('seller_id', $this->sellerId())
            ->whereKey($id)
            ->first()?->markRead();
    }

    public function markAllRead(): void
    {
        SellerNotification::query()
            ->where('seller_id', $this->sellerId())
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        $this->dispatch('$refresh');
    }

    public function clearRead(): void
    {
        SellerNotification::query()
            ->where('seller_id', $this->sellerId())
            ->where('is_read', true)
            ->delete();
    }

    /** JS bridge: the browser push permission changed. */
    public function setPushState(string $state): void
    {
        if (in_array($state, ['default', 'granted', 'denied', 'unsupported', 'subscribed'], true)) {
            $this->pushState = $state;
        }
    }

    /** JS bridge: device successfully registered for Web Push. */
    #[On('ip-push-subscribed')]
    public function pushSubscribed(): void
    {
        $this->pushState = 'subscribed';
    }

    /**
     * JS bridge: the browser reported an unsuitable push state on page
     * load (unsupported / denied) so the page shows the right hint
     * instead of a dead enable button.
     */
    #[On('ip-push-state')]
    public function browserPushState($state = null): void
    {
        if (is_array($state)) {
            $state = $state['state'] ?? null;
        }

        $this->setPushState((string) $state);
    }

    public function render()
    {
        $items = SellerNotification::query()
            ->where('seller_id', $this->sellerId())
            ->when($this->filter === 'unread', fn ($q) => $q->unread())
            ->latest()
            ->paginate(20);

        return view('livewire.seller.notifications.notifications-page', [
            'items'       => $items,
            'unreadCount' => SellerNotification::query()->where('seller_id', $this->sellerId())->unread()->count(),
        ])->title('نوتیفیکیشن‌ها');
    }
}
