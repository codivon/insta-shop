<?php

namespace App\Support\Sms\Drivers;

use App\Support\Phone;
use App\Support\Sms\Contracts\SmsDriver;
use App\Support\Sms\SmsMessage;
use App\Support\Sms\SmsResult;
use Illuminate\Support\Facades\Log;

/**
 * Test driver: writes the SMS to laravel.log instead of a real gateway.
 * Active by default so the whole OTP/login flow works without any paid
 * provider. The admin panel can switch providers anytime.
 */
class LogDriver implements SmsDriver
{
    public function name(): string
    {
        return 'log';
    }

    public function label(): string
    {
        return 'لاگ (تست بدون ارسال واقعی)';
    }

    public function settingsFields(): array
    {
        return [];
    }

    public function patternBased(): bool
    {
        return false;
    }

    public function send(SmsMessage $message): SmsResult
    {
        /*
         * SECURITY (v0.21.0): outside local the OTP body is masked — if the
         * admin ever leaves the log driver active on the production host,
         * valid login codes must not sit in plaintext inside laravel.log
         * (any log reader / log-download UI would expose account access).
         * The on-screen dev code (OtpService) is local-only anyway, so the
         * masked body only reduces debuggability in a misconfigured prod.
         */
        $body = app()->isLocal()
            ? $message->body()
            : preg_replace('/\b\d{4,8}\b/', '••••••', $message->body());

        Log::info('SMS[log] to='.Phone::toLocal($message->to)." template={$message->template} body=".$body);

        return SmsResult::success(providerMessageId: 'log-'.uniqid());
    }
}
