<?php

namespace App\Livewire\Admin\Auth;

use App\Models\Admin;
use App\Models\Setting;
use App\Support\ActivityLogger;
use App\Support\AdminNotifier;
use App\Support\Fmt;
use App\Support\ImageCaptcha;
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

    /* ── robot check — the answer lives ONLY in the session ── */
    public string $captcha = '';

    public function mount(): void
    {
        $this->regenerateCaptcha();
    }

    /**
     * Fresh challenge. When GD is available the admin gets a drawn PNG
     * (the answer is OCR-proof and never in the DOM); on exotic hosts
     * without GD the legacy math challenge stays as a fallback.
     */
    public function regenerateCaptcha(): void
    {
        $this->captcha = '';

        if (ImageCaptcha::available()) {
            ImageCaptcha::issue();

            return;
        }

        $a = random_int(2, 9);
        $b = random_int(1, 9);

        // the SUM is the secret; the two numbers are just for display
        session([
            'admin_captcha_sum'       => $a + $b,
            'admin_captcha_display_a' => $a,
            'admin_captcha_display_b' => $b,
        ]);

        $this->dispatch('admin-captcha-refresh', a: $a, b: $b);
    }

    /** The PNG data-URI of the current image challenge ('' → math fallback). */
    public function getCaptchaImageProperty(): string
    {
        return ImageCaptcha::available() ? ImageCaptcha::dataUri() : '';
    }

    /** Persian/Latin digits → int; null when not a number. */
    protected function captchaValue(string $input): ?int
    {
        $normalized = strtr(trim($input), [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        return preg_match('/^\d{1,3}$/', $normalized) ? (int) $normalized : null;
    }

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

        /* ── robot check first — no rate-limit burn for bots ── */
        if (ImageCaptcha::available()) {
            $captchaOk = ImageCaptcha::verify($this->captcha);
        } else {
            $expected  = (int) session('admin_captcha_sum', -1);
            $captchaOk = $expected >= 0 && $this->captchaValue($this->captcha) === $expected;
        }

        if (! $captchaOk) {
            $this->addError('captcha', 'کد امنیتی درست نیست.');

            $this->regenerateCaptcha();

            return;
        }

        [$max, $minutes] = $this->lockoutConfig();

        $key = $this->limiterKey();

        if (RateLimiter::tooManyAttempts($key, $max)) {
            $seconds = RateLimiter::availableIn($key);

            ActivityLogger::log('security.lockout', 'ورود پنل مدیریت موقتاً قفل شد — تلاش‌های ناموفق مکرر برای '.$this->email);

            AdminNotifier::notify(
                'security.lockout',
                'قفل ورود پنل مدیریت 🔒',
                'تلاش‌های ناموفق مکرر برای '.$this->email.' — ورود '.Fmt::fa((int) ceil($seconds / 60)).' دقیقه قفل شد.',
                route('admin.developer.settings'),
                'shield',
            );

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

            // a failed attempt must NEVER keep the same captcha alive
            $this->regenerateCaptcha();

            return;
        }

        RateLimiter::clear($key);
        ImageCaptcha::forget();
        session()->forget('admin_captcha_sum');

        session()->regenerate();

        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();
        $admin->forceFill(['last_login_at' => now()])->save();

        ActivityLogger::log('auth.login', 'مدیر «'.$admin->name.'» وارد پنل مدیریت شد');

        $this->redirect(route('admin.dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.auth.login', [
            'captchaImage' => $this->captchaImage,
        ]);
    }
}

