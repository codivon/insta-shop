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

    public function send(SmsMessage $message): SmsResult
    {
        Log::info("SMS[log] to=".Phone::toLocal($message->to)." template={$message->template} body=".$message->body());

        return SmsResult::success(providerMessageId: 'log-'.uniqid());
    }
}
