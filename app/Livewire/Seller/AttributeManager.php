<?php

namespace App\Livewire\Seller;

use App\Models\Shop;
use App\Models\ShopAttribute;
use App\Models\ShopAttributeValue;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.seller-slot')]
class AttributeManager extends Component
{
    /** new-attribute form */
    public string $name = '';

    public string $type = 'select';

    /** per-attribute new value inputs: [attributeId => value] */
    public array $newValue = [];

    /** per-attribute new color hex: [attributeId => #RRGGBB] */
    public array $newHex = [];

    protected function shop(): Shop
    {
        return once(fn () => Shop::ensureForSeller(auth('seller')->user()));
    }

    /** Add a new attribute field (نام + نوع ویژگی). */
    public function addAttribute(): void
    {
        $this->validate([
            'name' => [
                'required', 'string', 'min:1', 'max:80',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $taken = $this->shop()->attributes()
                        ->where('name', trim((string) $value))
                        ->exists();

                    if ($taken) {
                        $fail('ویژگی‌ای با این نام از قبل دارید.');
                    }
                },
            ],
            'type' => ['required', 'in:'.implode(',', array_keys(ShopAttribute::TYPES))],
        ], [
            'name.required' => 'نام ویژگی را وارد کنید (مثال: جنس، مدل، ...).',
            'name.max'      => 'نام ویژگی حداکثر ۸۰ کاراکتر است.',
        ]);

        $shop = $this->shop();

        $shop->attributes()->create([
            'name'  => trim($this->name),
            'type'  => $this->type,
            'sort'  => ((int) $shop->attributes()->max('sort')) + 1,
        ]);

        $this->name  = '';
        $this->type  = 'select';

        session()->flash('attr-flash', 'ویژگی جدید اضافه شد.');
    }

    public function deleteAttribute(int $attributeId): void
    {
        $attribute = $this->shop()->attributes()->whereKey($attributeId)->first();

        if ($attribute) {
            $attribute->delete();
            session()->flash('attr-flash', 'ویژگی حذف شد.');
        }
    }

    /** Add a value (مقدار) to an attribute. */
    public function addValue(int $attributeId): void
    {
        $attribute = $this->shop()->attributes()->whereKey($attributeId)->first();

        if (! $attribute) {
            return;
        }

        $value = trim((string) ($this->newValue[$attributeId] ?? ''));

        $this->validate([
            "newValue.$attributeId" => ['required', 'string', 'max:80'],
        ], [
            "newValue.$attributeId.required" => 'مقدار را وارد کنید.',
            "newValue.$attributeId.max"      => 'مقدار حداکثر ۸۰ کاراکتر است.',
        ]);

        $hex = null;

        if ($attribute->type === 'color') {
            $candidate = trim((string) ($this->newHex[$attributeId] ?? ''));

            if ($candidate !== '' && preg_match('/^#[0-9a-fA-F]{6}$/', $candidate)) {
                $hex = strtolower($candidate);
            }
        }

        $attribute->values()->create([
            'value'     => $value,
            'color_hex' => $hex,
            'sort'      => ((int) $attribute->values()->max('sort')) + 1,
        ]);

        $this->newValue[$attributeId] = '';
        $this->newHex[$attributeId]   = '#D62976';
    }

    public function deleteValue(int $valueId): void
    {
        $value = ShopAttributeValue::query()
            ->whereKey($valueId)
            ->whereHas('attribute', fn ($q) => $q->where('shop_id', $this->shop()->id))
            ->first();

        if ($value) {
            $value->delete();
        }
    }

    public function render()
    {
        return view('livewire.seller.attribute-manager', [
            'shop'         => $this->shop(),
            'attributeList' => $this->shop()->attributes()->with('values')->get(),
        ])->title('ویژگی‌های فروشگاه');
    }
}
