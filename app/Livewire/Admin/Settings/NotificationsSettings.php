<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\PushSubscription;
use App\Models\Setting;
use App\Support\AdminNotifier;
use App\Support\ActivityLogger;
use App\Support\WebPusher;
use Livewire\Component;

/**
 * Admin — «نوتیفیکیشن‌ها» settings section:
 *   • push master switch — send notifications to the phone/desktop even
 *     when the browser is closed (Web Push / VAPID);
 *   • per-event switches — which site events create admin notifications;
 *   • device manager — list + remove registered devices;
 *   • test buttons — fire a sample in-app + push notification;
 *   • VAPID key visibility + regeneration.
 * Every save is also written to the activity log with the before/after.
 */
class NotificationsSettings extends Component
{
    use NotifiesOnValidation;
    use GuardsPermissions;

    protected static string $permission = 'admin.settings.notifications';

    public bool $pushEnabled = false;

    /** @var array<string, bool> event key => enabled (admin panel) */
    public array $events = [];

    /** @var array<string, bool> event key => enabled (seller panel) */
    public array $sellerEvents = [];

    public string $vapidPublic = '';

    public string $testBody = '';

    public function mount(): void
    {
        $this->pushEnabled = (bool) Setting::get('notifications.push_enabled', false);

        /*
         * Event keys are dotted (order.new, wallet.withdraw …). A Blade
         * binding like wire:model="events.order.new" would be parsed by
         * Livewire as the NESTED path events→order→new, silently
         * disconnecting the checkbox from the flat array the component
         * reads in save(). The UI therefore uses underscore keys
         * (order_new) and save() maps them back to the dotted keys.
         */
        foreach (AdminNotifier::EVENTS as $key => $meta) {
            $this->events[str_replace('.', '__', $key)] = (bool) Setting::get('notifications.events.'.$key, true);
        }

        // seller-panel events (seller notifications + their pushes)
        foreach (\App\Support\SellerNotifier::EVENTS as $key => $meta) {
            $this->sellerEvents[str_replace('.', '__', $key)] = (bool) Setting::get('notifications.seller_events.'.$key, true);
        }

        $this->vapidPublic = (string) Setting::get('notifications.vapid_public', '');
    }

    public function save(): void
    {
        $before = [
            'notifications.push_enabled' => (bool) Setting::get('notifications.push_enabled', false),
        ];

        Setting::set('notifications.push_enabled', $this->pushEnabled ? '1' : '0', 'boolean');

        $after = ['notifications.push_enabled' => $this->pushEnabled];

        foreach (AdminNotifier::EVENTS as $key => $meta) {
            $before['notifications.events.'.$key] = (bool) Setting::get('notifications.events.'.$key, true);

            $uiKey = str_replace('.', '__', $key);

            Setting::set('notifications.events.'.$key, ! empty($this->events[$uiKey]) ? '1' : '0', 'boolean');

            $after['notifications.events.'.$key] = (bool) ! empty($this->events[$uiKey]);
        }

        foreach (\App\Support\SellerNotifier::EVENTS as $key => $meta) {
            $before['notifications.seller_events.'.$key] = (bool) Setting::get('notifications.seller_events.'.$key, true);

            $uiKey = str_replace('.', '__', $key);

            Setting::set('notifications.seller_events.'.$key, ! empty($this->sellerEvents[$uiKey]) ? '1' : '0', 'boolean');

            $after['notifications.seller_events.'.$key] = (bool) ! empty($this->sellerEvents[$uiKey]);
        }

        // keep the activity log honest — only log when something really changed
        $changedBefore = array_filter($before, fn ($v, $k) => ($after[$k] ?? null) !== $v, ARRAY_FILTER_USE_BOTH);

        ActivityLogger::log(
            'settings.updated',
            'تنظیمات نوتیفیکیشن‌ها ذخیره شد',
            before: $changedBefore,
            after: array_intersect_key($after, $changedBefore),
        );

        $this->dispatch('panel-toast', message: 'تنظیمات نوتیفیکیشن‌ها ذخیره شد. ✅', type: 'success');
    }

    /** Fire a test notification (in-app + push to all devices). */
    public function sendTest(): void
    {
        $pushed = WebPusher::broadcast('نوتیفیکیشن تستی InstaShop', 'اگر این پیام را روی گوشی/سیستم دیدید، پوش فعال است.', '/admin');

        AdminNotifier::notify('system.misc', 'نوتیفیکیشن تستی ارسال شد', 'این یک اعلان آزمایشی است — پوش به '.$pushed.' دستگاه ارسال شد.', '/admin/notifications', 'sparkles');

        ActivityLogger::log('notifications.test', 'نوتیفیکیشن تستی ارسال شد (پوش به '.$pushed.' دستگاه)');

        $this->testBody = 'اعلان تستی ارسال شد — پوش به '.\App\Support\Fmt::fa($pushed).' دستگاه.';
        $this->dispatch('panel-toast', message: 'نوتیفیکیشن تستی ارسال شد (پوش: '.$pushed.' دستگاه).', type: 'success');
    }

    public function regenerateKeys(): void
    {
        WebPusher::regenerateKeys();

        // old subscriptions are signed for the old key — drop them
        PushSubscription::query()->delete();

        $this->vapidPublic = (string) Setting::get('notifications.vapid_public', '');

        ActivityLogger::log('settings.updated', 'کلیدهای VAPID نوتیفیکیشن بازتولید شد (دستگاه‌ها باید دوباره فعال‌سازی کنند)');

        $this->dispatch('panel-toast', message: 'کلیدها بازتولید شد؛ دستگاه‌ها باید دوباره نوتیفیکیشن را فعال کنند.', type: 'success');
    }

    public function removeDevice(int $id): void
    {
        PushSubscription::query()->whereKey($id)->delete();

        ActivityLogger::log('settings.updated', 'یک دستگاه از نوتیفیکیشن‌های پوش حذف شد');

        $this->dispatch('panel-toast', message: 'دستگاه حذف شد.', type: 'success');
    }

    public function getDevicesProperty()
    {
        return PushSubscription::query()->latest('id')->limit(20)->get();
    }

    public function render()
    {
        return view('livewire.admin.settings.notifications-settings')
            ->title('تنظیمات نوتیفیکیشن‌ها');
    }
}
