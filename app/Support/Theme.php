<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Theme service — single source of truth for the app-wide palette.
 * Colors are stored in DB (settings group "theme") and editable from the
 * admin panel; every layout injects them as CSS variables.
 */
class Theme
{
    public const PRESETS = [
        'instagram'     => ['label' => 'گرادیان اینستاگرامی (پیش‌فرض)', 'hex' => '#D62976', 'hint' => 'فوشیا → صورتی → نارنجی', 'gradient' => 'from-fuchsia-600 via-pink-500 to-orange-500'],
        'sapphire'      => ['label' => 'آبی سافایر', 'hex' => '#2563EB', 'hint' => 'رنگ کلاسیک سامانه — آبی سوهان‌مدن', 'gradient' => 'from-blue-500 to-blue-700'],
        'royal-purple'  => ['label' => 'بنفش سلطنتی', 'hex' => '#7C3AED', 'hint' => 'نشیب مدرن و لاکچری', 'gradient' => 'from-purple-500 to-purple-800'],
        'emerald'       => ['label' => 'زمرد', 'hex' => '#059669', 'hint' => 'سبز رشد و اعتماد', 'gradient' => 'from-emerald-500 to-emerald-700'],
        'pacific'       => ['label' => 'اقیانوس آرام', 'hex' => '#0891B2', 'hint' => 'فیروزه‌ای عمیق و آرامش‌بخش', 'gradient' => 'from-cyan-500 to-teal-700'],
        'night-indigo'  => ['label' => 'نیلی شب', 'hex' => '#4F46E5', 'hint' => 'نیلی عمیق و رسمی', 'gradient' => 'from-indigo-500 to-indigo-800'],
        'fiery-sunset'  => ['label' => 'غروب آتشین', 'hex' => '#EA580C', 'hint' => 'نارنجی پویا و پرانرژی', 'gradient' => 'from-orange-500 to-red-600'],
        'warm-amber'    => ['label' => 'کهربای گرم', 'hex' => '#D97706', 'hint' => 'طلوع گنگ کرم و صمیمی', 'gradient' => 'from-amber-500 to-amber-700'],
        'crimson'       => ['label' => 'سرخ گنگون', 'hex' => '#DC2626', 'hint' => 'قرمز قدرتمند و برجسته', 'gradient' => 'from-red-500 to-rose-700'],
        'coral'         => ['label' => 'مرجانی', 'hex' => '#F43F5E', 'hint' => 'سرخی ملایم و شاد', 'gradient' => 'from-rose-400 to-rose-600'],
        'magenta'       => ['label' => 'ارغوانی تون', 'hex' => '#D946EF', 'hint' => 'سرخابی ماندگار و خلاق', 'gradient' => 'from-fuchsia-500 to-fuchsia-700'],
        'jasper-teal'   => ['label' => 'فیروزه خیزر', 'hex' => '#0D9488', 'hint' => 'ترکیبی از سبز و آبی و شادی‌بخش', 'gradient' => 'from-teal-500 to-teal-700'],
    ];

    /**
     * Multi-stop brand gradients per preset (CSS color stops). Presets not
     * listed here fall back to a two-stop gradient built from their own
     * shade scale (500 → 700) so every preset still gets a lively brand
     * gradient on primary surfaces.
     */
    public const GRADIENTS = [
        'instagram' => ['#C026D3', '#EC4899', '#F97316'], // fuchsia → pink → orange
    ];

    public const DEFAULTS = [
        'theme.preset'       => 'instagram',
        'theme.primary'      => '#D62976',
        'theme.default_mode' => 'light',
        'theme.app_name'     => 'InstaShop',
    ];

    /** Resolve effective theme values (DB overrides defaults). */
    public static function resolve(): array
    {
        $primary = Setting::get('theme.primary', self::DEFAULTS['theme.primary']);
        $preset  = Setting::get('theme.preset', self::DEFAULTS['theme.preset']);

        // keep preset pointing to "custom" when hex no longer matches its preset
        if ($preset !== 'custom' && ($hex = self::PRESETS[$preset]['hex'] ?? null) && strcasecmp($hex, $primary) !== 0) {
            $preset = 'custom';
        }

        return [
            'primary'       => Color::normalize($primary ?: self::DEFAULTS['theme.primary']),
            'preset'        => $preset,
            'default_mode'  => Setting::get('theme.default_mode', self::DEFAULTS['theme.default_mode']),
            'app_name'      => Setting::get('theme.app_name', self::DEFAULTS['theme.app_name']),
        ];
    }

    public static function shades(): array
    {
        return Color::shades(self::resolve()['primary']);
    }

    /** Multi-stop brand gradient for the resolved preset (CSS linear-gradient). */
    public static function gradient(): string
    {
        $preset  = self::resolve()['preset'];
        $stops   = self::GRADIENTS[$preset] ?? null;

        if (! $stops) {
            $shades = self::shades();
            $stops  = [$shades[500], $shades[700]];
        }

        return 'linear-gradient(135deg, '.implode(', ', $stops).')';
    }

    /** Build the ":root" CSS variable block content. */
    public static function cssVariables(): string
    {
        $shades = self::shades();
        $primary = self::resolve()['primary'];
        $lines = [];

        $lines[] = '--ip-primary: '.$primary.';';
        $lines[] = '--ip-on-primary: '.Color::onColor($primary).';';
        $lines[] = '--ip-gradient: '.self::gradient().';';

        foreach ($shades as $step => $hex) {
            $lines[] = "--ip-primary-{$step}: {$hex};";
        }

        return implode(' ', $lines);
    }

    /** Inline pre-paint script: applies dark class before first render (no flash). */
    public static function modeScript(): string
    {
        $default = self::resolve()['default_mode'] === 'dark' ? 'dark' : 'light';

        return "(function(){try{var m=localStorage.getItem('ip-mode')||'{$default}';if(m==='dark'){document.documentElement.classList.add('dark');}}catch(e){}})();";
    }
}
