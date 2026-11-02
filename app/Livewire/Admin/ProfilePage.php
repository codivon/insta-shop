<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Admin — «پروفایل مدیر»: edit your own display name + change the login
 * password (current password required). Available to EVERY admin — the
 * one page that touches the logged-in account itself.
 */
#[Layout('layouts.admin-slot')]
#[Title('پروفایل مدیر')]
class ProfilePage extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;

    protected static string $permission = 'admin.profile';

    public string $name = '';

    public string $email = '';

    /* ── password change ── */
    public string $currentPassword = '';

    public string $newPassword = '';

    public string $newPasswordConfirmation = '';

    public function mount(): void
    {
        $admin = auth('admin')->user();

        $this->name  = (string) ($admin?->name ?? '');
        $this->email = (string) ($admin?->email ?? '');
    }

    public function saveProfile(): void
    {
        $this->validateWithToast(fn () => $this->validate([
            'name'  => ['required', 'min:2', 'max:80'],
            'email' => ['required', 'email', 'max:190', 'unique:admins,email,'.(auth('admin')->id() ?? 0)],
        ], [
            'name.required'  => 'نام را وارد کنید.',
            'name.min'       => 'نام حداقل ۲ کاراکتر است.',
            'email.required' => 'ایمیل را وارد کنید.',
            'email.email'    => 'ایمیل معتبر نیست.',
            'email.unique'   => 'این ایمیل قبلاً ثبت شده است.',
        ]), 'ذخیره پروفایل ناموفق بود');

        $admin = auth('admin')->user();

        $before = ['name' => $admin->name, 'email' => $admin->email];

        $admin->name  = $this->name;
        $admin->email = $this->email;
        $admin->save();

        ActivityLogger::log(
            'profile.updated',
            'پروفایل مدیر بروزرسانی شد',
            subject: $admin,
            before: $before,
            after: ['name' => $admin->name, 'email' => $admin->email],
        );

        $this->dispatch('panel-toast', message: 'پروفایل با موفقیت ذخیره شد. ✅', type: 'success');
    }

    public function changePassword(): void
    {
        $this->validateWithToast(fn () => $this->validate([
            'currentPassword' => ['required', 'string'],
            'newPassword'     => ['required', 'string', 'min:8', 'max:64', 'confirmed', 'different:currentPassword'],
        ], [
            'currentPassword.required' => 'رمز عبور فعلی را وارد کنید.',
            'newPassword.required'     => 'رمز عبور جدید را وارد کنید.',
            'newPassword.min'          => 'رمز عبور جدید باید حداقل ۸ کاراکتر باشد.',
            'newPassword.confirmed'    => 'تکرار رمز عبور جدید مطابقت ندارد.',
            'newPassword.different'    => 'رمز جدید باید با رمز فعلی متفاوت باشد.',
        ]), 'تغییر رمز عبور ناموفق بود');

        $admin = auth('admin')->user();

        if (! Hash::check($this->currentPassword, (string) $admin->password)) {
            $this->addError('currentPassword', 'رمز عبور فعلی درست نیست.');

            $this->dispatch('panel-toast', message: 'رمز عبور فعلی درست نیست.', type: 'error');

            return;
        }

        $admin->password = $this->newPassword; // auto-hashed cast
        $admin->save();

        ActivityLogger::log('profile.password', 'رمز عبور مدیر تغییر کرد', subject: $admin);

        $this->currentPassword = '';
        $this->newPassword     = '';
        $this->newPasswordConfirmation = '';

        $this->dispatch('panel-toast', message: 'رمز عبور با موفقیت تغییر کرد. ✅', type: 'success');
    }

    public function render()
    {
        $admin = auth('admin')->user();

        return view('livewire.admin.profile-page', [
            'me' => $admin,
        ])->title('پروفایل مدیر')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — پروفایل مدیر']);
    }
}
