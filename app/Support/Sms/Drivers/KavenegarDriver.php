<?php

namespace App\Support\Sms\Drivers;

use App\Models\Setting;
use App\Support\Phone;
use App\Support\Sms\Contracts\SmsDriver;
use App\Support\Sms\SmsMessage;
use App\Support\Sms\SmsResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Kavenegar — Verify/Lookup API (template based).
 * Docs: https://kavenegar.com/rest.html  →  /v1/{API-KEY}/verify/lookup.json
 *
 * v0.18.1: the template name now comes ONLY from the SMS-template registry
 * (مرکز پیامک → «کد قرار گرفتن در پنل پیامک») — the per-provider OTP field
 * was removed from the settings tab. Variable mapping follows the proven
 * legacy implementation: data['code'] → token, plus explicit
 * token/token2/token3/token10/token20 passthrough.
 *
 * Settings: sms.kavenegar.api_key
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
            'api_key' => ['کلید API کاوه‌نگار', true],
        ];
    }

    public function patternBased(): bool
    {
        return true;
    }

    public function send(SmsMessage $message): SmsResult
    {
        $apiKey = (string) Setting::get('sms.kavenegar.api_key', '');
        $template = (string) $message->template;

        if ($apiKey === '') {
            return SmsResult::failure('تنظیمات کاوه‌نگار ناقص است (کلید API را در تنظیمات پیامک وارد کنید).');
        }

        if ($template === '') {
            return SmsResult::failure('نام قالب (Verify Lookup) برای این پیامک تنظیم نشده است — در «قالب‌های پیامک» فیلد «کد قرار گرفتن در پنل پیامک» را پر کنید.');
        }

        try {
            // Proven mapping: code → token; token2..token20 passthrough.
            $data = $message->data;
            $tokens = array_filter([
                'token' => (string) ($data['token'] ?? $data['code'] ?? ''),
                'token2' => isset($data['token2']) ? (string) $data['token2'] : null,
                'token3' => isset($data['token3']) ? (string) $data['token3'] : null,
                'token10' => isset($data['token10']) ? (string) $data['token10'] : null,
                'token20' => isset($data['token20']) ? (string) $data['token20'] : null,
            ], fn ($v) => $v !== null && $v !== '');

            $query = array_merge([
                'receptor' => Phone::toLocal($message->to),
                'template' => $template,
            ], $tokens);

            $response = Http::timeout(15)->get(
                "https://api.kavenegar.com/v1/{$apiKey}/verify/lookup.json",
                $query,
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
