<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\City;
use App\Models\Province;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Livewire\Concerns\NotifiesOnValidation;

/**
 * Admin — locations management (استان‌ها / شهرها).
 *
 * The seeded Iran province/city tree lives here: adding or removing
 * locations is ONLY possible from this panel page (sellers/shippers just
 * pick from the lists).
 */
#[Layout('layouts.admin-slot')]
class LocationsPage extends Component
{
    use NotifiesOnValidation;
    use GuardsPermissions;

    protected static string $permission = 'admin.locations';

    /** provinces | cities */
    public string $tab = 'provinces';

    /** City list province filter. */
    public int $provinceFilter = 0;

    public string $search = '';

    /* ── modal (create/edit both kinds) ── */
    public bool $modalOpen = false;

    /** null = create, int = editing id */
    public ?int $editingId = null;

    public string $kind = 'province'; // province | city

    public string $name = '';

    public int $parentProvinceId = 0; // city only

    public int $ordering = 0;

    public bool $isActive = true;

    public function updatingTab(): void
    {
        $this->resetPageState();
    }

    public function updatingProvinceFilter(): void
    {
        $this->resetPageState();
    }

    public function updatingSearch(): void
    {
        $this->resetPageState();
    }

    private function resetPageState(): void
    {
        // hook kept for future pagination
    }

    /* ─────────────── modal lifecycle ─────────────── */

    public function openCreate(string $kind): void
    {
        $this->resetErrorBag();
        $this->modalOpen  = true;
        $this->editingId  = null;
        $this->kind       = $kind === 'city' ? 'city' : 'province';
        $this->name       = '';
        $this->parentProvinceId = $this->tab === 'cities' && $this->provinceFilter > 0 ? $this->provinceFilter : 0;
        $this->ordering   = $this->nextOrdering();
        $this->isActive   = true;
    }

    public function openEdit(string $kind, int $id): void
    {
        $this->resetErrorBag();

        if ($kind === 'city') {
            $city = City::query()->find($id);

            if (! $city) {
                return;
            }

            $this->kind = 'city';
            $this->editingId = $city->id;
            $this->name = (string) $city->name;
            $this->parentProvinceId = (int) $city->province_id;
            $this->ordering = (int) $city->ordering;
            $this->isActive = (bool) $city->is_active;
        } else {
            $province = Province::query()->find($id);

            if (! $province) {
                return;
            }

            $this->kind = 'province';
            $this->editingId = $province->id;
            $this->name = (string) $province->name;
            $this->parentProvinceId = 0;
            $this->ordering = (int) $province->ordering;
            $this->isActive = (bool) $province->is_active;
        }

        $this->modalOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->editingId = null;
    }

    private function nextOrdering(): int
    {
        if ($this->kind === 'city') {
            $base = City::query()->when($this->parentProvinceId > 0, fn ($q) => $q->where('province_id', $this->parentProvinceId))->max('ordering');

            return (int) $base + 1;
        }

        return (int) Province::query()->max('ordering') + 1;
    }

    /* ─────────────── persistence ─────────────── */

    public function save(): void
    {
        $isCity = $this->kind === 'city';

        $this->validateWithToast(fn () => $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'parentProvinceId' => $isCity ? ['required', 'integer', 'exists:provinces,id'] : ['nullable', 'integer'],
            'ordering' => ['integer', 'min:0', 'max:99999'],
            'isActive' => ['boolean'],
        ], [
            'name.required' => 'نام را وارد کنید.',
            'parentProvinceId.required' => 'استان شهر را انتخاب کنید.',
        ]));

        if ($isCity) {
            $unique = City::query()
                ->where('province_id', $this->parentProvinceId)
                ->where('name', trim($this->name))
                ->when($this->editingId, fn ($q) => $q->whereKeyNot($this->editingId))
                ->exists();

            if ($unique) {
                $this->addError('name', 'این شهر در این استان قبلاً ثبت شده است.');

                return;
            }

            $city = $this->editingId ? City::query()->find($this->editingId) : new City;
            $city->fill([
                'province_id' => $this->parentProvinceId,
                'name'        => trim($this->name),
                'ordering'    => $this->ordering,
                'is_active'   => $this->isActive,
            ])->save();
        } else {
            $unique = Province::query()
                ->where('name', trim($this->name))
                ->when($this->editingId, fn ($q) => $q->whereKeyNot($this->editingId))
                ->exists();

            if ($unique) {
                $this->addError('name', 'این استان قبلاً ثبت شده است.');

                return;
            }

            $province = $this->editingId ? Province::query()->find($this->editingId) : new Province;
            $province->fill([
                'name'      => trim($this->name),
                'ordering'  => $this->ordering,
                'is_active' => $this->isActive,
            ])->save();
        }

        $this->closeModal();

        $this->dispatch('locations-toast', message: $this->editingId ? 'به‌روزرسانی شد. ✅' : 'با موفقیت اضافه شد. ✅');
    }

    public function deleteLocation(string $kind, int $id): void
    {
        if ($kind === 'city') {
            City::query()->find($id)?->delete();

            return;
        }

        // deleting a province cascades its cities (FK) — sellers may have
        // carriers pointing at them; nullOnDelete keeps those rows intact.
        Province::query()->find($id)?->delete();
    }

    /* ─────────────── computed ─────────────── */

    public function getProvincesProperty(): Collection
    {
        return Province::query()
            ->withCount('cities')
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('ordering')
            ->orderBy('id')
            ->get();
    }

    public function getCitiesProperty(): Collection
    {
        return City::query()
            ->join('provinces', 'provinces.id', '=', 'cities.province_id')
            ->when($this->provinceFilter > 0, fn ($q) => $q->where('cities.province_id', $this->provinceFilter))
            ->when($this->search !== '', fn ($q) => $q->where('cities.name', 'like', '%'.$this->search.'%'))
            ->orderBy('provinces.ordering')
            ->orderBy('cities.ordering')
            ->get([
                'cities.id', 'cities.name', 'cities.province_id',
                'cities.ordering', 'cities.is_active', 'provinces.name as province_name',
            ]);
    }

    public function getProvinceOptionsProperty(): Collection
    {
        return Province::query()->orderBy('ordering')->orderBy('id')->get(['id', 'name']);
    }

    public function getStatsProperty(): array
    {
        return [
            'provinces' => Province::query()->count(),
            'cities'    => City::query()->count(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.locations-page', [
            'provinces'       => $this->provinces,
            'cities'          => $this->cities,
            'provinceOptions' => $this->provinceOptions,
            'stats'           => $this->stats,
        ])->title('استان‌ها و شهرها');
    }
}
