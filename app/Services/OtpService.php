<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Support\Sms\SmsManager;
use App\Support\Sms\SmsMessage;

/**
 * Phone OTP lifecycle: issue → send → verify, with per-phone throttles.
 *
 * Rules (configurable via settings, group "otp"):
 *  - code is 6 digits, expires in 5 minutes
 *  - max 5 verification attempts per code
 *  - resend cooldown of 60 seconds per phone+panel+purpose
 *
 * In local environments with the "log" SMS driver, the plain code is
 * returned to the caller (dev_code) so login can be tested on screen.
 */
class OtpService
{
    public const EXPIRY_MINUTES = 5;
    public const MAX_ATTEMPTS   = 5;
    public const RESEND_SECONDS = 60;

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

        // Invalidate any previous active code for this phone/panel/purpose
        OtpCode::where('phone', $phone)
            ->where('panel', $panel)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        $code = (string) random_int(100000, 999999);

        $otp = OtpCode::create([
            'phone'        => $phone,
            'code'         => $code,
            'purpose'      => $purpose,
            'panel'        => $panel,
            'attempts'     => 0,
            'max_attempts' => self::MAX_ATTEMPTS,
            'expires_at'   => now()->addMinutes(self::EXPIRY_MINUTES),
        ]);

        $result = $this->sms->send(
            SmsMessage::make($phone)
                ->template('otp', ['code' => $code, 'minutes' => self::EXPIRY_MINUTES])
                ->panel($panel),
        );

        if (! $result->ok) {
            $otp->markConsumed(); // never leave a usable code behind a failed send

            return ['ok' => false, 'retry_after' => null, 'dev_code' => null, 'error' => 'ارسال پیامک ناموفق بود: '.$result->error];
        }

        return [
            'ok'         => true,
            'retry_after' => null,
            'dev_code'   => $this->devCodeEnabled() ? $code : null,
            'error'      => null,
        ];
    }

    /**
     * Verify the code. Consumes it on success; counts an attempt on failure.
     */
    public function verify(string $phone, string $panel, string $code): array
    {
        $otp = OtpCode::activeFor($phone, $panel)->first();

        if (! $otp) {
            return ['ok' => false, 'error' => 'کدی یافت نشد. دوباره درخواست کد بدهید.'];
        }

        if (! $otp->isValid()) {
            return ['ok' => false, 'error' => 'کد منقضی شده است. کد جدید بگیرید.'];
        }

        if (! hash_equals($otp->code, (string) $code)) {
            $otp->incrementAttempts();

            $left = $otp->max_attempts - $otp->attempts;

            return [
                'ok'       => false,
                'error'    => $left > 0
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

    /** Show the code on screen during local testing with the log driver. */
    protected function devCodeEnabled(): bool
    {
        return app()->isLocal()
            && app(SmsManager::class)->activeDriverName() === 'log';
    }
}
