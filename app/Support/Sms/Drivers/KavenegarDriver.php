<?php

namespace App\Support\Sms\Drivers;

use App\Support\Phone;
use App\Support\Sms\Contracts\SmsDriver;
use App\Support\Sms\SmsMessage;
use App\Support\Sms\SmsResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Kavenegar — Verify/Lookup API (template based).
 * Docs: https://kavenegar.com/rest.html  →  /v1/{API-KEY}/verify/lookup.json
 * Settings: sms.kavenegar.api_key, sms.kavenegar.template_otp
 */
class KavenegarDriver implements SmsDriver
{
    public function name(): string
    {
        return 'kavenegar';
    }

    public function label(): string
    {
        return 'کاوه‌نگار (Verify-Lookup)';
    }

    public function settingsFields(): array
    {
        return [
            'api_key'    => ['کلید API کاوه‌نگار', true],
            'template_otp' => ['نام قالب OTP در کاوه‌نگار', true],
        ];
    }

    public function send(SmsMessage $message): SmsResult
    {
        $apiKey   = (string) \App\Models\Setting::get('sms.kavenegar.api_key', '');
        $template = $message->template ?: (string) \App\Models\Setting::get('sms.kavenegar.template_otp', '');

        if ($apiKey === '' || $template === '') {
            return SmsResult::failure('تنظیمات کاوه‌نگار ناقص است (api_key یا template_otp).');
        }

        try {
            $response = Http::timeout(15)->get(
                "https://api.kavenegar.com/v1/{$apiKey}/verify/lookup.json",
                [
                    'receptor' => Phone::toLocal($message->to),
                    'token'    => (string) ($message->data['code'] ?? ''),
                    'template' => $template,
                ],
            );

            $json = $response->json() ?? [];

            if (! $response->successful() || (($json['return']['status'] ?? 500) != 200)) {
                return SmsResult::failure(
                    'کاوه‌نگار: '.($json['return']['message'] ?? 'خطای ناشناخته'),
                    $json,
                );
            }

            return SmsResult::success(
                providerMessageId: (string) ($json['entries'][0]['messageid'] ?? ''),
                raw: $json,
            );
        } catch (ConnectionException $e) {
            return SmsResult::failure('کاوه‌نگار: خطای اتصال — '.$e->getMessage());
        } catch (\Throwable $e) {
            return SmsResult::failure('کاوه‌نگار: '.$e->getMessage());
        }
    }
}
