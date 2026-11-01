<?php

namespace App\Livewire\Admin\Access;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Support\AdminPermissions;
use App\Models\AdminRole;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Admin — «نقش‌ها» (زیرمنوی مدیران).
 *
 * Roles list + a fully MODAL add/edit dialog with grouped permission
 * checkboxes (every admin page = a permission; developer pages are
 * intentionally absent — they are super-admin only). System roles cannot
 * be deleted.
 */
#[Layout('layouts.admin-slot')]
#[Title('نقش‌ها و دسترسی‌ها')]
class RolesIndex extends Component
{
    use GuardsPermissions;

    protected static string $permission = 'admin.access.roles';

    /* ── modal state ── */
    public bool $modalOpen = false;

    public ?int $roleId = null;

    public bool $editing = false;

    /* ── role form ── */
    public string $roleName = '';

    public string $roleDescription = '';

    /** @var array<string, bool> permission slug => checked */
    public array $permissionSelection = [];

    public function mount(): void
    {
        $this->resetForm();
    }

    /* ── modal flow ── */

    public function createRole(): void
    {
        $this->resetForm();
        $this->editing   = false;
        $this->roleId    = null;
        $this->modalOpen = true;
    }

    public function editRole(int $id): void
    {
        $role = AdminRole::query()->findOrFail($id);

        $this->roleId           = $role->id;
        $this->roleName         = $role->name;
        $this->roleDescription  = (string) $role->description;
        $this->permissionSelection = array_fill_keys(
            array_keys(AdminPermissions::all()),
            false,
        );

        foreach ($role->permissions ?? [] as $slug) {
            $this->permissionSelection[$slug] = true;
        }

        $this->editing   = true;
        $this->modalOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->resetForm();
    }

    public function saveRole(): void
    {
        $this->validate([
            'roleName'        => ['required', 'min:2', 'max:80'],
            'roleDescription' => ['nullable', 'max:255'],
        ], [
            'roleName.required' => 'نام نقش را وارد کنید.',
        ]);

        $permissions = array_keys(array_filter(
            $this->permissionSelection,
            fn ($checked) => (bool) $checked,
        ));

        $role = $this->roleId
            ? AdminRole::query()->findOrFail($this->roleId)
            : new AdminRole;

        $role->name        = $this->roleName;
        $role->description = $this->roleDescription ?: null;
        $role->permissions = $permissions;

        if (! $this->roleId) {
            $role->slug = $this->uniqueSlug($this->roleName);
        }

        $role->save();

        $this->modalOpen = false;
        $this->resetForm();

        // Close the dialog on the client too (Alpine state lives there).
        $this->dispatch('roles-close-modal');

        session()->flash('admin-roles-flash', $this->roleId
            ? 'نقش با موفقیت بروزرسانی شد.'
            : 'نقش جدید ایجاد شد.');
    }

    public function deleteRole(int $id): void
    {
        $role = AdminRole::query()->findOrFail($id);

        if ($role->is_system) {
            session()->flash('admin-roles-error', 'نقش سیستمی قابل حذف نیست.');

            return;
        }

        $role->admins()->detach();
        $role->delete();

        session()->flash('admin-roles-flash', 'نقش حذف شد.');
    }

    protected function resetForm(): void
    {
        $this->roleId          = null;
        $this->roleName        = '';
        $this->roleDescription = '';
        $this->resetSelection();
        $this->resetErrorBag();
    }

    protected function resetSelection(): void
    {
        $this->permissionSelection = array_fill_keys(array_keys(AdminPermissions::all()), false);
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'role';

        $slug = $base;
        $i    = 1;

        while (AdminRole::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    /* ── permission helpers for the view ── */

    public function groups(): array
    {
        return AdminPermissions::groups();
    }

    public function toggleGroup(string $groupKey, bool $on): void
    {
        foreach ($this->groups()[$groupKey]['permissions'] ?? [] as $slug => $label) {
            $this->permissionSelection[$slug] = $on;
        }
    }

    public function groupChecked(string $groupKey): bool
    {
        $slugs = array_keys($this->groups()[$groupKey]['permissions'] ?? []);

        return $slugs !== [] && collect($slugs)->every(fn ($s) => $this->permissionSelection[$s] ?? false);
    }

    /* ── data for view ── */

    public function render()
    {
        return view('livewire.admin.access.roles-index', [
            'roles'    => AdminRole::query()->withCount('admins')->orderBy('id')->get(),
        ])->title('نقش‌ها و دسترسی‌ها')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — مدیران — نقش‌ها']);
    }
}
