<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Support\Sms\SmsManager;
use App\Support\Sms\SmsTemplates;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Phone OTP lifecycle: issue → send → verify, with per-phone throttles.
 *
 * Rules (configurable via settings, group "otp"):
 *  - code is 6 digits, expires in 5 minutes
 *  - max 5 verification attempts per code
 *  - resend cooldown of 60 seconds per phone+panel+purpose
 *  - send-quota: max 8 codes/phone/hour + 20 codes/IP/hour (SMS pumping)
 *
 * SECURITY:
 *  - codes are stored only as an HMAC-SHA256 digest (never plaintext)
 *  - the on-screen dev code (log driver) exists ONLY in local
 *    environments — on any production install codes go to the phone,
 *    never to the browser, even when the test/log driver is active.
 */
class OtpService
{
    public const EXPIRY_MINUTES = 5;

    public const MAX_ATTEMPTS = 5;

    public const RESEND_SECONDS = 60;

    /** Max codes per phone per hour (harassment guard). */
    public const PHONE_HOURLY_LIMIT = 8;

    /** Max codes per IP per hour (SMS-pumping guard). */
    public const IP_HOURLY_LIMIT = 20;

    public function __construct(protected SmsManager $sms) {}

    /**
     * Generate + send an OTP.
     *
     * @return array{ok: bool, retry_after: int|null, dev_code: string|null, error: string|null}
     */
    public function send(string $phone, string $panel, string $purpose = 'login'): array
    {
        $cooldown = $this->cooldownRemaining($phone, $panel, $purpose);

        if ($cooldown > 0) {
            return ['ok' => false, 'retry_after' => $cooldown, 'dev_code' => null, 'error' => 'کد قبلاً ارسال شده. لطفاً '.$cooldown.' ثانیه صبر کنید.'];
        }

        /* ── send-quota guards (SMS pumping / harassment): per-IP + per-phone ── */
        $ipKey = 'otp-ip:'.sha1((string) request()->ip());
        $phoneKey = 'otp-phone:'.sha1($phone.'|'.$panel);

        foreach ([
            [$ipKey, self::IP_HOURLY_LIMIT, 'تعداد درخواست کد از سمت شما بیش از حد مجاز است. لطفاً بعداً تلاش کنید.'],
            [$phoneKey, self::PHONE_HOURLY_LIMIT, 'برای این شماره زیاد کد ارسال شده است. لطفاً یک ساعت دیگر تلاش کنید.'],
        ] as [$quotaKey, $quotaMax, $quotaError]) {
            if (RateLimiter::tooManyAttempts($quotaKey, $quotaMax)) {
                return ['ok' => false, 'retry_after' => (int) min(3600, RateLimiter::availableIn($quotaKey)), 'dev_code' => null, 'error' => $quotaError];
            }
        }

        // Invalidate any previous active code for this phone/panel/purpose
        OtpCode::where('phone', $phone)
            ->where('panel', $panel)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        $code = (string) random_int(100000, 999999);

        $otp = OtpCode::create([
            'phone' => $phone,
            'code' => $this->hash($code), // only the digest is stored — never the plaintext
            'purpose' => $purpose,
            'panel' => $panel,
            'attempts' => 0,
            'max_attempts' => self::MAX_ATTEMPTS,
            'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
        ]);

        $result = $this->sms->send(
            SmsTemplates::make(
                'otp_login',
                $phone,
                ['code' => $code, 'minutes' => self::EXPIRY_MINUTES],
                $panel,
            ),
        );

        if (! $result->ok) {
            $otp->markConsumed(); // never leave a usable code behind a failed send

            return ['ok' => false, 'retry_after' => null, 'dev_code' => null, 'error' => 'ارسال پیامک ناموفق بود: '.$result->error];
        }

        RateLimiter::hit($ipKey, 3600);
        RateLimiter::hit($phoneKey, 3600);

        return [
            'ok' => true,
            'retry_after' => null,
            'dev_code' => $this->devCodeEnabled() ? $code : null,
            'error' => null,
        ];
    }

    /**
     * Verify the code. Consumes it on success; counts an attempt on failure.
     * When $purpose is given, only codes issued for that purpose match.
     */
    public function verify(string $phone, string $panel, string $code, ?string $purpose = null): array
    {
        $query = OtpCode::activeFor($phone, $panel);

        if ($purpose !== null) {
            $query->where('purpose', $purpose);
        }

        $otp = $query->first();

        if (! $otp) {
            return ['ok' => false, 'error' => 'کدی یافت نشد. دوباره درخواست کد بدهید.'];
        }

        if (! $otp->isValid()) {
            return ['ok' => false, 'error' => 'کد منقضی شده است. کد جدید بگیرید.'];
        }

        if (! hash_equals($otp->code, $this->hash((string) $code))) {
            $otp->incrementAttempts();

            $left = $otp->max_attempts - $otp->attempts;

            return [
                'ok' => false,
                'error' => $left > 0
                    ? 'کد وارد شده نادرست است ('.$left.' تلاش باقی مانده).'
                    : 'کد نادرست وارد شد؛ حساب موقتاً قفل شد. کد جدید بگیرید.',
            ];
        }

        $otp->markConsumed();

        return ['ok' => true, 'error' => null];
    }

    /** Seconds until the phone may request a new code (0 = allowed). */
    public function cooldownRemaining(string $phone, string $panel, string $purpose = 'login'): int
    {
        $last = OtpCode::where('phone', $phone)
            ->where('panel', $panel)
            ->where('purpose', $purpose)
            ->latest()
            ->first();

        if (! $last) {
            return 0;
        }

        $elapsed = $last->created_at->diffInSeconds(now(), false);

        return max(0, self::RESEND_SECONDS - (int) $elapsed);
    }

    /**
     * HMAC digest of an OTP code — codes are NEVER stored in plaintext, so
     * a leaked database/backup exposes no usable one-time codes.
     */
    protected function hash(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    /**
     * Show the code on screen ONLY in local environments when the admin has
     * switched SMS to the test/log driver. SECURITY: never on production —
     * the log driver (the default before a provider is configured) must NOT
     * become an on-screen account-takeover channel for real installs.
     */
    protected function devCodeEnabled(): bool
    {
        return app()->isLocal()
            && app(SmsManager::class)->activeDriverName() === 'log';
    }
}
