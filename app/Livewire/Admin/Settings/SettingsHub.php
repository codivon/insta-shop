<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\Admin;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Admin — Settings HUB («تنظیمات سیستم»).
 *
 * ONE sidebar menu; every section (عمومی / ظاهر / پیامک / درگاه پرداخت) is
 * rendered INSIDE this page with an internal sidebar — exactly like the
 * reference design. Each section keeps its own permission so roles can
 * grant only some of them; tabs the admin cannot open are hidden from the
 * internal navigation. (قالب‌های پیامک is a STANDALONE page reached from
 * the «پیامک و پرووایدر» section — not part of this hub.)
 *
 * The section components are embedded Livewire children, so their state,
 * validation and permission guards stay untouched.
 */
#[Layout('layouts.admin-slot')]
#[Title('تنظیمات سیستم')]
class SettingsHub extends Component
{
    use GuardsPermissions;

    protected static string $permission = 'admin.settings';

    /**
     * The hub opens for the umbrella permission OR any single section
     * permission — each tab inside stays individually guarded.
     */
    public function bootGuardsPermissions(): void
    {
        /** @var Admin|null $admin */
        $admin = auth('admin')->user();

        if ($admin === null) {
            abort(401, 'دسترسی مجاز نیست.');
        }

        $anySection = collect([
            'admin.settings.general',
            'admin.settings.landing',
            'admin.settings.appearance',
            'admin.settings.sms',
            'admin.settings.payment',
            'admin.settings.shops',
            'admin.settings.stories',
            'admin.settings.notifications',
            'admin.settings.backups',
        ])->contains(fn (string $p) => $admin->hasPermission($p));

        if ($admin->isSuper() || $admin->hasPermission('admin.settings') || $anySection) {
            return;
        }

        abort(403, 'دسترسی لازم برای صفحه تنظیمات را ندارید.');
    }

    /** Active section — kept in the URL so tabs are shareable/back-friendly. */
    #[Url]
    public string $tab = 'general';

    /** Section registry: key => [icon, label, description, permission]. */
    public function sections(): array
    {
        $admin = auth('admin')->user();
        $can = fn (string $perm) => $admin === null ? false : $admin->hasPermission($perm);

        $map = [
            'general' => [
                'icon' => 'sliders',
                'label' => 'تنظیمات عمومی',
                'description' => 'عنوان، لوگو و فاوآیکون سایت',
                'perm' => 'admin.settings.general',
            ],
            'landing' => [
                'icon' => 'layout-grid',
                'label' => 'صفحهٔ اصلی',
                'description' => 'متن‌ها، ترتیب و نمایش بخش‌های لندینگ',
                'perm' => 'admin.settings.landing',
            ],
            'appearance' => [
                'icon' => 'palette',
                'label' => 'ظاهر و رنگ‌بندی',
                'description' => 'پالت‌ها، رنگ اصلی و حالت پیش‌فرض',
                'perm' => 'admin.settings.appearance',
            ],
            'sms' => [
                'icon' => 'message',
                'label' => 'پیامک و پرووایدر',
                'description' => 'پروایدر ارسال و اعتبارنامه‌ها',
                'perm' => 'admin.settings.sms',
            ],
            'payment' => [
                'icon' => 'credit-card',
                'label' => 'درگاه پرداخت',
                'description' => 'فعال‌سازی چند درگاه هم‌زمان، اعتبارنامه‌ها و پرداخت تسهیمی (زرین‌پال، زیبال، سپ سامان، سداد، به‌پرداخت…)',
                'perm' => 'admin.settings.payment',
            ],
            'shops' => [
                'icon' => 'store',
                'label' => 'تنظیمات فروشگاه‌ها',
                'description' => 'تایید خودکار فروشنده و کمیسیون',
                'perm' => 'admin.settings.shops',
            ],
            'stories' => [
                'icon' => 'zap',
                'label' => 'تنظیمات استوری',
                'description' => 'سقف استوری فعال هر فروشگاه و حالت تایید',
                'perm' => 'admin.settings.stories',
            ],
            'notifications' => [
                'icon' => 'bell',
                'label' => 'نوتیفیکیشن‌ها',
                'description' => 'اعلان رویدادها + پوش گوشی و سیستم',
                'perm' => 'admin.settings.notifications',
            ],
            'backups' => [
                'icon' => 'database',
                'label' => 'پشتیبان‌گیری خودکار',
                'description' => 'زمان‌بندی خودکار، نگهداری و ایمیل پشتیبان (SMTP)',
                'perm' => 'admin.settings.backups',
            ],
        ];

        // Hide sections this admin cannot open.
        return array_filter($map, fn ($s) => $can($s['perm']));
    }

    public function mount(): void
    {
        // Fall back to the first accessible section.
        $keys = array_keys($this->sections());

        if (! in_array($this->tab, $keys, true)) {
            $this->tab = $keys[0] ?? 'general';
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.settings-hub', [
            'sections' => $this->sections(),
        ])->title('تنظیمات سیستم')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — تنظیمات']);
    }
}
