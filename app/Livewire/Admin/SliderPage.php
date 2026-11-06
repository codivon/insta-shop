<?php

namespace App\Livewire\Admin;

use App\Models\Banner;

/**
 * Admin — home HERO slider slides (اسلایدر صفحهٔ اصلی, position=home_hero).
 * Same mechanics as the banner cards page, but a SEPARATE page since
 * v0.17.1 (اسلایدر / بنرها split) and a wide 12:5 preview.
 */
class SliderPage extends BannersPage
{
    /**
     * SECURITY (v0.20.0): the page route is guarded by the route name
     * (admin.slider — now also registered in the permission registry so
     * roles CAN grant it); the component previously inherited «admin.banners»
     * here, so the Livewire actions and the page URL checked DIFFERENT
     * permissions. Both are now the same key.
     */
    protected static string $permission = 'admin.slider';

    protected string $position = Banner::POSITION_HERO;

    protected string $sectionTitle = 'اسلایدر صفحهٔ اصلی';

    protected string $breadcrumb = 'مدیریت کل — اسلایدر صفحهٔ اصلی';

    public function render()
    {
        return view('livewire.admin.banners-page', [
            'banners' => $this->banners,
            'sectionTitle' => $this->sectionTitle,
            'heroAspect' => true,
        ])->title($this->sectionTitle)
            ->layout('layouts.admin-slot', ['breadcrumb' => $this->breadcrumb]);
    }
}
