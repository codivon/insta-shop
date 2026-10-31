<?php

namespace App\Support\Sms\Drivers;

use App\Support\Phone;
use App\Support\Sms\Contracts\SmsDriver;
use App\Support\Sms\SmsMessage;
use App\Support\Sms\SmsResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * FarazSMS / IPPanel — pattern (variable) API.
 * Docs: https://www.ippanel.com/api  →  POST /api/v1/sms/pattern/normal/send
 * Settings: sms.ippanel.apikey, sms.ippanel.sender, sms.ippanel.pattern_otp
 */
class FarazIPPanelDriver implements SmsDriver
{
    public function name(): string
    {
        return 'ippanel';
    }

    public function label(): string
    {
        return 'فراز اس‌ام‌اس / IPPanel (ارسال الگو)';
    }

    public function settingsFields(): array
    {
        return [
            'apikey'      => ['کلید API (apikey)', true],
            'sender'      => ['خط ارسال (شماره خط)', true],
            'pattern_otp' => ['کد الگوی OTP', true],
        ];
    }

    public function send(SmsMessage $message): SmsResult
    {
        $apikey  = (string) \App\Models\Setting::get('sms.ippanel.apikey', '');
        $sender  = (string) \App\Models\Setting::get('sms.ippanel.sender', '');
        $pattern = $message->template ?: (string) \App\Models\Setting::get('sms.ippanel.pattern_otp', '');

        if ($apikey === '' || $pattern === '') {
            return SmsResult::failure('تنظیمات IPPanel ناقص است (apikey یا pattern_otp).');
        }

        try {
            $response = Http::timeout(15)->withHeaders(['apikey' => $apikey])
                ->post('https://api2.ippanel.com/api/v1/sms/pattern/normal/send', [
                    'code'      => $pattern,
                    'sender'    => $sender,
                    'recipient' => Phone::toLocal($message->to),
                    'variable'  => ['code' => (string) ($message->data['code'] ?? '')],
                ]);

            $json = $response->json() ?? [];

            $metaOk = (($json['code'] ?? null) == 200)
                || (($json['metadata']['status'] ?? null) === true)
                || $response->status() === 200 && isset($json['data']);

            if (! $response->successful() || ! $metaOk) {
                return SmsResult::failure(
                    'IPPanel: '.($json['message'] ?? $json['errorMessage'] ?? 'خطای ناشناخته'),
                    $json,
                );
            }

            return SmsResult::success(
                providerMessageId: (string) data_get($json, 'data.0.message_id', data_get($json, 'data.message_id', '')),
                raw: $json,
            );
        } catch (ConnectionException $e) {
            return SmsResult::failure('IPPanel: خطای اتصال — '.$e->getMessage());
        } catch (\Throwable $e) {
            return SmsResult::failure('IPPanel: '.$e->getMessage());
        }
    }
}
