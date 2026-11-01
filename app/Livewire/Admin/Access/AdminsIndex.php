<?php

namespace App\Livewire\Admin\Access;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\Admin;
use App\Models\AdminRole;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Livewire\Concerns\NotifiesOnValidation;

/**
 * Admin — «لیست مدیران» (زیرمنوی مدیران).
 *
 * Full CRUD over admin accounts, entirely modal-based: افزودن/ویرایش in a
 * dialog (name, email, password, roles), delete with confirm. Super admins
 * are system rows: they can only be managed by other supers and can never
 * be demoted from here.
 */
#[Layout('layouts.admin-slot')]
#[Title('لیست مدیران')]
class AdminsIndex extends Component
{
    use NotifiesOnValidation;
    use GuardsPermissions;

    protected static string $permission = 'admin.admins';

    /* ── modal state ── */
    public bool $modalOpen = false;

    public ?int $editingId = null;

    /* ── admin form ── */
    public string $adminName = '';

    public string $adminEmail = '';

    public string $adminPassword = '';

    /** @var array<int, bool> roleId => checked */
    public array $roleSelection = [];

    /* ── delete confirm ── */
    public ?int $deleteId = null;

    public function mount(): void
    {
        $this->resetForm();
    }

    /* ── modal flow ── */

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->modalOpen = true;
    }

    public function openEdit(int $id): void
    {
        $admin = Admin::query()->findOrFail($id);
        $me    = auth('admin')->user();

        if ($admin->isSuper() && ! $me?->isSuper()) {
            session()->flash('admin-admins-error', 'فقط مدیر اصلی می‌تواند حساب مدیر اصلی را ویرایش کند.');

            return;
        }

        $this->resetForm();
        $this->editingId    = $admin->id;
        $this->adminName    = $admin->name;
        $this->adminEmail   = $admin->email;
        $this->roleSelection = array_fill_keys(
            AdminRole::query()->pluck('id')->map(fn ($v) => (int) $v)->all(),
            false,
        );

        foreach ($admin->roles as $role) {
            $this->roleSelection[$role->id] = true;
        }

        $this->modalOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->adminName     = '';
        $this->adminEmail    = '';
        $this->adminPassword = '';
        $this->roleSelection = array_fill_keys(
            AdminRole::query()->pluck('id')->map(fn ($v) => (int) $v)->all(),
            false,
        );
        $this->resetErrorBag();
    }

    public function saveAdmin(): void
    {
        $creating = $this->editingId === null;

        $rules = [
            'adminName'  => ['required', 'min:2', 'max:80'],
            'adminEmail' => ['required', 'email', 'max:190', 'unique:admins,email'.($creating ? '' : ','.$this->editingId)],
            'adminPassword' => $creating
                ? ['required', 'string', 'min:8', 'max:64']
                : ['nullable', 'string', 'min:8', 'max:64'],
        ];

        $messages = [
            'adminName.required'     => 'نام مدیر را وارد کنید.',
            'adminEmail.required'    => 'ایمیل مدیر را وارد کنید.',
            'adminEmail.unique'      => 'این ایمیل قبلاً ثبت شده است.',
            'adminPassword.required' => 'رمز عبور (حداقل ۸ کاراکتر) را وارد کنید.',
            'adminPassword.min'      => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
        ];

        $this->validateWithToast(fn () => $this->validate($rules, $messages));

        $me = auth('admin')->user();

        if ($creating && ! $me?->isSuper()) {
            session()->flash('admin-admins-error', 'افزودن مدیر جدید فقط توسط مدیر اصلی مجاز است.');

            return;
        }

        $admin = $creating ? new Admin : Admin::query()->findOrFail($this->editingId);

        if (! $creating && $admin->isSuper() && ! $me?->isSuper()) {
            session()->flash('admin-admins-error', 'فقط مدیر اصلی می‌تواند حساب مدیر اصلی را ویرایش کند.');

            return;
        }

        $admin->name  = $this->adminName;
        $admin->email = $this->adminEmail;

        if ($this->adminPassword !== '') {
            $admin->password = $this->adminPassword; // auto-hashed cast
        }

        $admin->save();

        // Roles: only manageable on non-super accounts.
        if (! $admin->isSuper()) {
            $roleIds = array_keys(array_filter($this->roleSelection, fn ($v) => (bool) $v));
            $admin->roles()->sync($roleIds);
        }

        $this->modalOpen = false;
        $this->resetForm();

        // Close the dialog on the client too (Alpine state lives there).
        $this->dispatch('admins-close-modal');

        session()->flash('admin-admins-flash', $creating
            ? 'مدیر جدید با موفقیت اضافه شد.'
            : 'اطلاعات مدیر بروزرسانی شد.');
    }

    public function requestDelete(int $id): void
    {
        $this->deleteId = $id;
    }

    public function deleteAdmin(): void
    {
        if ($this->deleteId === null) {
            return;
        }

        $admin = Admin::query()->find($this->deleteId);
        $me    = auth('admin')->user();

        $this->deleteId = null;

        if (! $admin) {
            return;
        }

        if (! $me?->isSuper()) {
            session()->flash('admin-admins-error', 'حذف مدیر فقط توسط مدیر اصلی مجاز است.');

            return;
        }

        if ($admin->id === $me->id) {
            session()->flash('admin-admins-error', 'نمی‌توانید حساب خودتان را حذف کنید.');

            return;
        }

        if ($admin->isSuper()) {
            session()->flash('admin-admins-error', 'حساب مدیر اصلی قابل حذف نیست.');

            return;
        }

        $admin->roles()->detach();
        $admin->delete();

        session()->flash('admin-admins-flash', 'مدیر حذف شد.');
    }

    /* ── data for view ── */

    public function render()
    {
        $me = auth('admin')->user();

        return view('livewire.admin.access.admins-index', [
            'admins'   => Admin::query()->with('roles')->orderBy('id')->get(),
            'allRoles' => AdminRole::query()->orderBy('id')->get(),
            'me'       => $me,
        ])->title('لیست مدیران')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — مدیران — لیست مدیران']);
    }
}
