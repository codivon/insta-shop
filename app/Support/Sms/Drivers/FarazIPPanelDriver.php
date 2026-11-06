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
 * IPPanel (آی‌پی‌پنل / فراز) — legacy pattern API with panel
 * username + password, ported 1:1 from the customer's proven
 * implementation (worked in production in their previous shop):
 *
 *   GET https://ippanel.com/patterns/pattern
 *       ?username=…&password=…&from=…&to=["09…"]
 *       &input_data={"…":"…"}&pattern_code=…
 *   (POST fields: the raw input_data map — replicated as form params)
 *
 * v0.18.1: pattern code comes ONLY from the SMS-template registry
 * (مرکز پیامک) — the per-provider OTP field was removed.
 *
 * Settings: sms.ippanel.username, sms.ippanel.password, sms.ippanel.from
 */
class FarazIPPanelDriver implements SmsDriver
{
    public function name(): string
    {
        return 'ippanel';
    }

    public function label(): string
    {
        return 'آی‌پی‌پنل (ippanel.com — پترن با نام کاربری)';
    }

    public function settingsFields(): array
    {
        return [
            'username' => ['نام کاربری پنل', true],
            'password' => ['رمز عبور پنل', true],
            'from' => ['خط ارسال (شماره خط)', true],
        ];
    }

    public function patternBased(): bool
    {
        return true;
    }

    public function send(SmsMessage $message): SmsResult
    {
        $username = (string) Setting::get('sms.ippanel.username', '');
        $password = (string) Setting::get('sms.ippanel.password', '');
        $from = (string) Setting::get('sms.ippanel.from', '');
        $pattern = (string) $message->template;

        if ($username === '' || $password === '' || $from === '') {
            return SmsResult::failure('تنظیمات آی‌پی‌پنل ناقص است (نام کاربری، رمز عبور و خط ارسال را در تنظیمات پیامک وارد کنید).');
        }

        if ($pattern === '') {
            return SmsResult::failure('کد پترن برای این پیامک تنظیم نشده است — در «قالب‌های پیامک» فیلد «کد قرار گرفتن در پنل پیامک» را پر کنید.');
        }

        try {
            $inputData = $message->data !== [] ? $message->data : ['code' => ''];

            $url = 'https://ippanel.com/patterns/pattern?'.http_build_query([
                'username' => $username,
                'password' => $password,
                'from' => $from,
                'to' => json_encode([Phone::toLocal($message->to)]),
                'input_data' => json_encode($inputData, JSON_UNESCAPED_UNICODE),
                'pattern_code' => $pattern,
            ]);

            // Proven implementation posts the input_data map as body fields
            // alongside the query string — replicated exactly.
            $response = Http::timeout(15)->asForm()->post($url, $inputData);

            $body = (string) $response->body();
            $json = json_decode($body, true) ?? [];

            $ok = $response->successful()
                && ($json !== [])
                && (($json['code'] ?? null) === 'ok'
                    || (($json['code'] ?? null) !== 'error' && isset($json['data'])));

            if (! $ok) {
                return SmsResult::failure(
                    'آی‌پی‌پنل: '.($json['message'] ?? $json['error_message'] ?? mb_substr($body, 0, 180) ?: 'خطای ناشناخته'),
                    $json ?: ['raw' => $body],
                );
            }

            $packageId = data_get($json, 'data.package_id')
                ?? data_get($json, 'data.message_id')
                ?? data_get($json, 'data.0.message_id');

            return SmsResult::success(
                providerMessageId: (string) (is_array($packageId) ? json_encode($packageId, JSON_UNESCAPED_UNICODE) : ($packageId ?? '')),
                raw: $json,
            );
        } catch (ConnectionException $e) {
            return SmsResult::failure('آی‌پی‌پنل: خطای اتصال — '.$e->getMessage());
        } catch (\Throwable $e) {
            return SmsResult::failure('آی‌پی‌پنل: '.$e->getMessage());
        }
    }
}
