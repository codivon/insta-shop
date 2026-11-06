<?php

namespace App\Livewire\Admin\Notifications;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\AdminNotification;
use App\Support\WebPusher;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Admin — topbar NOTIFICATIONS BELL.
 *
 *  • Polls every 30s for the unread count + latest items;
 *  • a dropdown lists the recent notifications (click → open link +
 *    mark read), «علامت‌گذاری همه» reads everything;
 *  • «فعال‌سازی نوتیفیکیشن دستگاه» registers this browser for real
 *    Web Push (works even when the browser is closed) through /sw.js.
 */
class NotificationsBell extends Component
{
    use GuardsPermissions;

    /**
     * SECURITY (v0.20.0): this bell used to run with NO auth check at all —
     * /livewire/update is panel-less, so a seller/customer/guest session
     * could legally reach these actions (mark read/unread all, open links)
     * had it ever obtained a checksummed snapshot. bootGuardsPermissions()
     * re-runs on every mount/hydrate: guests → 401, any authenticated admin
     * → allowed (the bell is global topbar UI), other panels → 401.
     */
    protected static ?string $permission = null;

    public bool $open = false;

    /** Browser push permission state: default|granted|denied|unsupported|subscribed */
    public string $pushState = 'default';

    public string $vapidKey = '';

    public function mount(): void
    {
        $this->vapidKey = WebPusher::publicKey();

        $this->pushState = $this->vapidKey === '' ? 'unsupported' : 'default';
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function getUnreadCountProperty(): int
    {
        return AdminNotification::query()->unread()->count();
    }

    public function getItemsProperty()
    {
        return AdminNotification::query()->latest()->limit(8)->get();
    }

    public function markRead(int $id): void
    {
        AdminNotification::query()->whereKey($id)->first()?->markRead();
    }

    /**
     * client v0.17.15: «وقتی مشاهده میزنم، اون نوتیف باید خوانده شده باشه» —
     * ONE server action replaces the racing wire:navigate + wire:click
     * raced: the navigation cancelled the markRead commit (the exact
     * «AbortError: signal is aborted without reason» the client reported)
     * so notifications stayed unread. ONE action now marks read and only
     * then navigates, server-side — no race is possible.
     */
    public function openNotification(int $id): void
    {
        $item = AdminNotification::query()->whereKey($id)->first();

        if ($item === null) {
            return;
        }

        if (! $item->is_read) {
            $item->markRead();
        }

        if ($item->link) {
            $this->redirect($item->link, navigate: true);
        }
    }

    public function markAllRead(): void
    {
        AdminNotification::query()->unread()->update(['is_read' => true, 'read_at' => now()]);

        $this->dispatch('$refresh');
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
     * load (unsupported / denied) so the UI can show the right hint
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
        return view('livewire.admin.notifications.notifications-bell');
    }
}
