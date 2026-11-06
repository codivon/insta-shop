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
 * فراز اس‌ام‌اس (ایران‌پیامک) — pattern API with a panel Api-Key,
 * ported 1:1 from the customer's proven implementation (worked in
 * production in their previous shop):
 *
 *   POST https://api.iranpayamak.com/ws/v1/sms/pattern
 *   Headers: Accept: application/json, Api-Key: …, Content-Type: application/json
 *   Body: { code, attributes, recipient, line_number, number_format: "english" }
 *
 * v0.18.1: NEW driver (name "farazsms") — pattern code comes ONLY from
 * the SMS-template registry (مرکز پیامک).
 *
 * Settings: sms.farazsms.api_key, sms.farazsms.from
 */
class FarazSmsDriver implements SmsDriver
{
    public function name(): string
    {
        return 'farazsms';
    }

    public function label(): string
    {
        return 'فراز اس‌ام‌اس (ایران‌پیامک — پترن با کلید API)';
    }

    public function settingsFields(): array
    {
        return [
            'api_key' => ['کلید API (Api-Key)', true],
            'from' => ['خط ارسال (شماره خط)', true],
        ];
    }

    public function patternBased(): bool
    {
        return true;
    }

    public function send(SmsMessage $message): SmsResult
    {
        $apiKey = (string) Setting::get('sms.farazsms.api_key', '');
        $from = (string) Setting::get('sms.farazsms.from', '');
        $pattern = (string) $message->template;

        if ($apiKey === '' || $from === '') {
            return SmsResult::failure('تنظیمات فراز اس‌ام‌اس ناقص است (کلید API و خط ارسال را در تنظیمات پیامک وارد کنید).');
        }

        if ($pattern === '') {
            return SmsResult::failure('کد پترن برای این پیامک تنظیم نشده است — در «قالب‌های پیامک» فیلد «کد قرار گرفتن در پنل پیامک» را پر کنید.');
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Api-Key' => $apiKey,
                ])
                ->post('https://api.iranpayamak.com/ws/v1/sms/pattern', [
                    'code' => $pattern,
                    'attributes' => $message->data !== [] ? $message->data : (object) [],
                    'recipient' => Phone::toLocal($message->to),
                    'line_number' => $from,
                    'number_format' => 'english',
                ]);

            $body = (string) $response->body();
            $json = json_decode($body, true) ?? [];

            $metaOk = (($json['code'] ?? null) == 200)
                || (($json['metadata']['status'] ?? null) === true)
                || (($json['status'] ?? null) === 'success')
                || ($response->successful() && isset($json['data']));

            if (! $response->successful() || ! $metaOk) {
                return SmsResult::failure(
                    'فراز اس‌ام‌اس: '.($json['message'] ?? $json['errorMessage'] ?? mb_substr($body, 0, 180) ?: 'خطای ناشناخته'),
                    $json ?: ['raw' => $body],
                );
            }

            $messageId = data_get($json, 'data.0.message_id')
                ?? data_get($json, 'data.message_id')
                ?? data_get($json, 'data.track_id')
                ?? data_get($json, 'data.0.id');

            return SmsResult::success(
                providerMessageId: (string) (is_array($messageId) ? json_encode($messageId, JSON_UNESCAPED_UNICODE) : ($messageId ?? '')),
                raw: $json,
            );
        } catch (ConnectionException $e) {
            return SmsResult::failure('فراز اس‌ام‌اس: خطای اتصال — '.$e->getMessage());
        } catch (\Throwable $e) {
            return SmsResult::failure('فراز اس‌ام‌اس: '.$e->getMessage());
        }
    }
}
