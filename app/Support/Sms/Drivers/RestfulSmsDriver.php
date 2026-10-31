<?php

namespace App\Support\Sms\Drivers;

use App\Support\Phone;
use App\Support\Sms\Contracts\SmsDriver;
use App\Support\Sms\SmsMessage;
use App\Support\Sms\SmsResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * RestfulSms (SMS.ir legacy REST API — Ebrahimzadeh style).
 * Docs: https://restfulsms.com/api/SendSms
 * Settings: sms.restfulsms.api_token, sms.restfulsms.line_number
 */
class RestfulSmsDriver implements SmsDriver
{
    public function name(): string
    {
        return 'restfulsms';
    }

    public function label(): string
    {
        return 'ابراهیم‌زاده / RestfulSms (SMS.ir)';
    }

    public function settingsFields(): array
    {
        return [
            'api_token'   => ['توکن API (x-sms-ir-api-token)', true],
            'line_number' => ['خط ارسال (اختیاری)', false],
        ];
    }

    public function send(SmsMessage $message): SmsResult
    {
        $token      = (string) \App\Models\Setting::get('sms.restfulsms.api_token', '');
        $lineNumber = (string) \App\Models\Setting::get('sms.restfulsms.line_number', '');

        if ($token === '') {
            return SmsResult::failure('تنظیمات RestfulSms ناقص است (api_token).');
        }

        try {
            $payload = [
                'mobile' => [Phone::toLocal($message->to)],
                'text'   => $message->body(),
            ];

            if ($lineNumber !== '') {
                $payload['lineNumber'] = $lineNumber;
            }

            $response = Http::timeout(15)
                ->withHeaders(['x-sms-ir-api-token' => $token])
                ->post('https://restfulsms.com/api/SendSms', $payload);

            $json = $response->json() ?? [];

            if (! $response->successful() || (($json['IsSuccessful'] ?? false) !== true)) {
                return SmsResult::failure(
                    'RestfulSms: '.($json['Message'] ?? 'خطای ناشناخته'),
                    $json,
                );
            }

            return SmsResult::success(
                providerMessageId: (string) data_get($json, 'Value.PackageId', ''),
                raw: $json,
            );
        } catch (ConnectionException $e) {
            return SmsResult::failure('RestfulSms: خطای اتصال — '.$e->getMessage());
        } catch (\Throwable $e) {
            return SmsResult::failure('RestfulSms: '.$e->getMessage());
        }
    }
}
