<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use App\Support\Color;
use App\Support\Theme;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin-slot')]
class AppearanceSettings extends Component
{
    public string $preset = 'sapphire';

    public string $primary = '#2563EB';

    public string $defaultMode = 'light';

    public function mount(): void
    {
        $theme = Theme::resolve();

        $this->preset = $theme['preset'];
        $this->primary = $theme['primary'];
        $this->defaultMode = $theme['default_mode'];
    }

    public function updatedPreset(string $value): void
    {
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

    public function getShadesProperty(): array
    {
        return Color::isHex($this->primary)
            ? Color::shades($this->primary)
            : Color::shades(Theme::DEFAULTS['theme.primary']);
    }

    public function save(): void
    {
        $this->validate([
            'preset' => ['nullable', 'in:'.implode(',', array_keys(Theme::PRESETS)).',custom'],
            'primary' => ['required', 'string'],
            'defaultMode' => ['required', 'in:light,dark'],
        ]);

        if (! Color::isHex($this->primary)) {
            $this->addError('primary', 'کد رنگ نامعتبر است. نمونه صحیح: #2563EB');

            return;
        }

        Setting::setMany([
            'theme.preset' => $this->preset,
            'theme.primary' => Color::normalize($this->primary),
            'theme.default_mode' => $this->defaultMode,
        ]);

        session()->flash('appearance-saved', 'تنظیمات ظاهر با موفقیت ذخیره شد و روی همه پنل‌ها اعمال گردید.');
    }

    public function resetTheme(): void
    {
        $this->preset = Theme::DEFAULTS['theme.preset'];
        $this->primary = Theme::DEFAULTS['theme.primary'];
        $this->defaultMode = Theme::DEFAULTS['theme.default_mode'];

        Setting::setMany([
            'theme.preset' => $this->preset,
            'theme.primary' => $this->primary,
            'theme.default_mode' => $this->defaultMode,
        ]);

        session()->flash('appearance-saved', 'تم به حالت پیش‌فرض (آبی سافایر) بازگشت.');
    }

    public function render()
    {
        return view('livewire.admin.appearance-settings')
            ->title('تنظیمات — ظاهر و رنگ‌بندی');
    }
}
