<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;

use App\Models\Setting;
use App\Support\Color;
use App\Support\Theme;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin-slot')]
class AppearanceSettings extends Component
{
    use GuardsPermissions;

    protected static string $permission = 'admin.settings.appearance';

    /** Warmth slider range (hue degrees, ±). */
    public const WARMTH_MIN = -30;

    public const WARMTH_MAX = 30;

    public string $preset = 'instagram';

    public string $primary = '#D62976';

    /** Hue-shift of the selected preset (گرمی/سردی) — presets only. */
    public int $warmth = 0;

    public string $defaultMode = 'light';

    public function mount(): void
    {
        $theme = Theme::resolve();

        $this->preset = $theme['preset'];
        $this->primary = $theme['primary'];
        $this->warmth = (int) $theme['warmth'];
        $this->defaultMode = $theme['default_mode'];
    }

    public function updatedPreset(string $value): void
    {
        // a fresh preset always starts from its base color
        $this->warmth = 0;

        if ($value !== 'custom' && isset(Theme::PRESETS[$value])) {
            $this->primary = Theme::PRESETS[$value]['hex'];
        }
    }

    public function updatedPrimary(string $value): void
    {
        if (Color::isHex($value)) {
            $this->primary = Color::normalize($value);
            $this->preset = 'custom';
        }
    }

    /** Effective primary color: preset hex hue-shifted by warmth, or the custom color. */
    public function effectivePrimary(): string
    {
        if ($this->preset !== 'custom' && isset(Theme::PRESETS[$this->preset])) {
            $base = Theme::PRESETS[$this->preset]['hex'];

            return $this->warmth !== 0 ? Color::shiftHue($base, $this->warmth) : $base;
        }

        return Color::isHex($this->primary)
            ? Color::normalize($this->primary)
            : Theme::DEFAULTS['theme.primary'];
    }

    public function getShadesProperty(): array
    {
        return Color::shades($this->effectivePrimary());
    }

    /** «پیش‌فرض» button under the warmth slider — back to the palette's base hue. */
    public function resetWarmth(): void
    {
        $this->warmth = 0;

        if ($this->preset !== 'custom' && isset(Theme::PRESETS[$this->preset])) {
            $this->primary = Theme::PRESETS[$this->preset]['hex'];
        }

        $this->save();

        session()->flash('appearance-saved', 'میزان گرمی و سردی پالت به حالت پیش‌فرض بازگشت.');
    }

    public function save(): void
    {
        $this->validate([
            'preset' => ['nullable', 'in:'.implode(',', array_keys(Theme::PRESETS)).',custom'],
            'primary' => ['required', 'string'],
            'warmth' => ['required', 'integer', 'min:'.self::WARMTH_MIN, 'max:'.self::WARMTH_MAX],
            'defaultMode' => ['required', 'in:light,dark'],
        ], [
            'warmth.min' => 'میزان گرمی خارج از محدوده مجاز است.',
            'warmth.max' => 'میزان گرمی خارج از محدوده مجاز است.',
        ]);

        if ($this->preset === 'custom' && ! Color::isHex($this->primary)) {
            $this->addError('primary', 'کد رنگ نامعتبر است. نمونه صحیح: #D62976');

            return;
        }

        Setting::setMany([
            'theme.preset' => $this->preset,
            'theme.primary' => Color::normalize($this->effectivePrimary()),
            'theme.warmth' => $this->preset === 'custom' ? 0 : $this->warmth,
            'theme.default_mode' => $this->defaultMode,
        ]);

        // keep the form in sync with what was stored
        $this->primary = Color::normalize($this->effectivePrimary());

        session()->flash('appearance-saved', 'تنظیمات ظاهر با موفقیت ذخیره شد و روی همه پنل‌ها اعمال گردید.');
    }

    public function resetTheme(): void
    {
        $this->preset = Theme::DEFAULTS['theme.preset'];
        $this->primary = Theme::DEFAULTS['theme.primary'];
        $this->warmth = 0;
        $this->defaultMode = Theme::DEFAULTS['theme.default_mode'];

        Setting::setMany([
            'theme.preset' => $this->preset,
            'theme.primary' => $this->primary,
            'theme.warmth' => 0,
            'theme.default_mode' => $this->defaultMode,
        ]);

        session()->flash('appearance-saved', 'تم به حالت پیش‌فرض (گرادیان اینستاگرامی) بازگشت.');
    }

    public function render()
    {
        return view('livewire.admin.appearance-settings')
            ->title('تنظیمات — ظاهر و رنگ‌بندی');
    }
}
