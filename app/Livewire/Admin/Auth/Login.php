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
use Illuminate\Support\Facades\Cache;
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
            'admin_captcha_sum' => $a + $b,
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
     *
     * SECURITY (three independent buckets — one leaked bucket must never
     * mean unlimited guessing):
     *  - email+IP   : the classic per-administrator bucket
     *  - email only : distributed attacks (IP rotation) still hit a
     *                 GLOBAL ceiling per account
     *  - IP only    : one source hammering many accounts is capped
     */
    protected function limiterKeys(): array
    {
        $email = sha1(mb_strtolower(trim($this->email)));
        $ip = sha1((string) request()->ip());

        return [
            'admin-login:'.sha1($email.'|'.$ip),
            'admin-login-email:'.$email,
            'admin-login-ip:'.$ip,
        ];
    }

    protected function lockoutConfig(): array
    {
        $max = 5;
        $minutes = 10;

        try {
            if (SchemaReady::check('settings')) {
                $max = max(3, (int) Setting::get('security.login_max_attempts', 5));
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

        /* ── robot check first — captcha failures are throttled per IP ── */
        $captchaKey = 'admin-captcha-fail:'.sha1((string) request()->ip());

        if (RateLimiter::tooManyAttempts($captchaKey, 15)) {
            $this->addError('captcha', 'تلاش‌های ناموفق بیش از حد مجاز است. چند دقیقه دیگر دوباره تلاش کنید.');

            return;
        }

        if (ImageCaptcha::available()) {
            $captchaOk = ImageCaptcha::verify($this->captcha);
        } elseif (app()->isProduction()) {
            /*
             * SECURITY: the math fallback (2 + 3 = ?) is fully visible in
             * the DOM — scriptable by any bot. On production hosts without
             * GD we FAIL CLOSED instead of degrading to a solvable riddle:
             * the operator must enable GD (or solve the deployment), not
             * the attacker bypass the challenge.
             */
            $this->addError('captcha', 'کپچای تصویری روی این سرور در دسترس نیست (افزونهٔ GD فعال نیست) — ورود متوقف است. با سازنده تماس بگیرید.');

            return;
        } else {
            $expected = (int) session('admin_captcha_sum', -1);
            $captchaOk = $expected >= 0 && $this->captchaValue($this->captcha) === $expected;
        }

        if (! $captchaOk) {
            RateLimiter::hit($captchaKey, 600);

            $this->addError('captcha', 'کد امنیتی درست نیست.');

            $this->regenerateCaptcha();

            return;
        }

        [$max, $minutes] = $this->lockoutConfig();

        $keys = $this->limiterKeys();

        // email-only bucket gets a wider ceiling (one admin from several IPs
        // is normal); the IP-only bucket covers many-accounts-one-source.
        $ceilings = [$max, $max * 3, $max * 6];

        foreach (array_map(null, $keys, $ceilings) as [$key, $ceiling]) {
            if (RateLimiter::tooManyAttempts($key, $ceiling)) {
                $seconds = max(
                    RateLimiter::availableIn($key),
                    RateLimiter::availableIn($keys[0]),
                );

                $this->notifyLockoutOnce($keys[0], $minutes);

                $this->addError('email', 'به دلیل تلاش‌های ناموفق مکرر، ورود موقتاً قفل شده است. لطفاً '
                    .Fmt::fa((int) ceil($seconds / 60)).' دقیقه دیگر دوباره تلاش کنید.');

                return;
            }
        }

        $ok = Auth::guard('admin')->attempt(
            ['email' => $this->email, 'password' => $this->password],
            $this->remember,
        );

        if (! $ok) {
            foreach (array_map(null, $keys, $ceilings) as [$key, $ceiling]) {
                RateLimiter::hit($key, $minutes * 60);
            }

            $left = max(0, $max - RateLimiter::attempts($keys[0]));

            $this->addError('email', 'ایمیل یا رمز عبور نادرست است.'
                .($left > 0 && $left <= 2 ? ' '.Fmt::fa($left).' تلاش باقی مانده است.' : ''));

            // a failed attempt must NEVER keep the same captcha alive
            $this->regenerateCaptcha();

            return;
        }

        foreach ($keys as $key) {
            RateLimiter::clear($key);
        }

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

    /**
     * Notify about a lockout ONCE per lockout window — an attacker hammering
     * a locked account must not be able to flood the admin notification feed.
     */
    protected function notifyLockoutOnce(string $primaryKey, int $minutes): void
    {
        $flag = 'admin-lockout-notified:'.sha1($primaryKey);

        if (! Cache::add($flag, true, now()->addMinutes($minutes))) {
            return;
        }

        ActivityLogger::log('security.lockout', 'ورود پنل مدیریت موقتاً قفل شد — تلاش‌های ناموفق مکرر برای '.$this->email);

        AdminNotifier::notify(
            'security.lockout',
            'قفل ورود پنل مدیریت 🔒',
            'تلاش‌های ناموفق مکرر برای '.$this->email.' — ورود '.Fmt::fa($minutes).' دقیقه قفل شد.',
            route('admin.developer.settings'),
            'shield',
        );
    }
}
