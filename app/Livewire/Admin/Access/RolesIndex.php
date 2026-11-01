<?php

namespace App\Livewire\Admin\Access;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\Admin;
use App\Models\AdminRole;
use App\Support\AdminPermissions;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Admin — Roles & permissions («دسترسی‌ها»).
 *
 * Tabs: نقش‌ها (create/edit/delete roles with permission checkboxes,
 * grouped exactly like the sidebar sections) and مدیران (assign roles to
 * admins). Super admins bypass everything — they are marked in the list.
 */
#[Layout('layouts.admin-slot')]
#[Title('نقش‌ها و دسترسی‌ها')]
class RolesIndex extends Component
{
    use GuardsPermissions;

    protected static string $permission = 'admin.access.roles';

    public string $tab = 'roles'; // roles | admins

    /* ── role form ── */
    public ?int $roleId = null;

    public string $roleName = '';

    public string $roleDescription = '';

    /** @var array<string, bool> permission slug => checked */
    public array $permissionSelection = [];

    public bool $editing = false;

    /* ── admin role matrix ── */
    /** @var array<int, array<int, bool>> adminId => roleId => checked */
    public array $assignments = [];

    public function mount(): void
    {
        $this->resetSelection();
        $this->loadAssignments();
    }

    protected function resetSelection(): void
    {
        $this->permissionSelection = array_fill_keys(array_keys(AdminPermissions::all()), false);
    }

    protected function loadAssignments(): void
    {
        $this->assignments = [];

        foreach (Admin::query()->orderBy('id')->get() as $admin) {
            $this->assignments[$admin->id] = [];
        }

        foreach (\DB::table('admin_role')->get() as $pivot) {
            $this->assignments[$pivot->admin_id][$pivot->role_id] = true;
        }
    }

    /* ── CRUD ── */

    public function createRole(): void
    {
        $this->resetForm();
        $this->editing = true;
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

        $this->editing = true;
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

        $this->editing = false;
        $this->resetForm();

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

        $this->loadAssignments();

        session()->flash('admin-roles-flash', 'نقش حذف شد.');
    }

    protected function resetForm(): void
    {
        $this->roleId          = null;
        $this->roleName        = '';
        $this->roleDescription = '';
        $this->resetSelection();
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

    /* ── admin assignments ── */

    public function saveAssignments(): void
    {
        foreach ($this->assignments as $adminId => $roleMap) {
            $admin = Admin::query()->find($adminId);

            if (! $admin || $admin->isSuper()) {
                continue; // super admins keep full access
            }

            $roleIds = array_keys(array_filter($roleMap ?? [], fn ($v) => $v));

            $admin->roles()->sync($roleIds);
        }

        session()->flash('admin-roles-flash', 'دسترسی مدیران ذخیره شد.');
    }

    /* ── data for view ── */

    public function render()
    {
        return view('livewire.admin.access.roles-index', [
            'roles'  => AdminRole::query()->withCount('admins')->orderBy('id')->get(),
            'admins' => Admin::query()->orderBy('id')->get(),
            'allRoles' => AdminRole::query()->orderBy('id')->get(),
        ])->title('نقش‌ها و دسترسی‌ها')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — تنظیمات — دسترسی‌ها']);
    }
}
