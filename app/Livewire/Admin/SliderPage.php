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
