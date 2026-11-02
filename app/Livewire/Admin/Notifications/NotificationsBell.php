<?php

namespace App\Livewire\Admin\Notifications;

use App\Models\AdminNotification;
use App\Support\WebPusher;
use Livewire\Component;
use Livewire\Attributes\On;

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
