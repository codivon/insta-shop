<?php

namespace App\Livewire\Admin\Auth;

use App\Models\Admin;
use App\Models\Setting;
use App\Support\Fmt;
use App\Support\SchemaReady;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin-auth')]
#[Title('ورود مدیر')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'ایمیل را وارد کنید.',
            'email.email' => 'ایمیل معتبر وارد کنید.',
            'password.required' => 'رمز عبور را وارد کنید.',
        ];
    }

    /**
     * Brute-force guard, configurable in Developer Settings → امنیت
     * (security.login_max_attempts / security.login_lockout_minutes).
     */
    protected function limiterKey(): string
    {
        return 'admin-login:'.sha1(mb_strtolower(trim($this->email)).'|'.(string) request()->ip());
    }

    protected function lockoutConfig(): array
    {
        $max = 5;
        $minutes = 10;

        try {
            if (SchemaReady::check('settings')) {
                $max     = max(3, (int) Setting::get('security.login_max_attempts', 5));
                $minutes = max(1, (int) Setting::get('security.login_lockout_minutes', 10));
            }
        } catch (\Throwable) {
            // fall back to defaults
        }

        return [$max, $minutes];
    }

    public function login(): void
    {
        $this->resetErrorBag();
        $this->validate();

        [$max, $minutes] = $this->lockoutConfig();

        $key = $this->limiterKey();

        if (RateLimiter::tooManyAttempts($key, $max)) {
            $seconds = RateLimiter::availableIn($key);

            $this->addError('email', 'به دلیل تلاش‌های ناموفق مکرر، ورود موقتاً قفل شده است. لطفاً '
                .Fmt::fa((int) ceil($seconds / 60)).' دقیقه دیگر دوباره تلاش کنید.');

            return;
        }

        $ok = Auth::guard('admin')->attempt(
            ['email' => $this->email, 'password' => $this->password],
            $this->remember,
        );

        if (! $ok) {
            RateLimiter::hit($key, $minutes * 60);

            $left = max(0, $max - RateLimiter::attempts($key));

            $this->addError('email', 'ایمیل یا رمز عبور نادرست است.'
                .($left > 0 && $left <= 2 ? ' '.Fmt::fa($left).' تلاش باقی مانده است.' : ''));

            return;
        }

        RateLimiter::clear($key);

        session()->regenerate();

        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();
        $admin->forceFill(['last_login_at' => now()])->save();

        $this->redirect(route('admin.dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.auth.login');
    }
}

