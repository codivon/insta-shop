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
 * SMS.ir (سرویس REST قدیمی RestfulSms.com — همان «اپراتور ابراهیم‌زاده»).
 *
 * v0.18.1: rewritten 1:1 from the customer's proven implementation —
 * the free-text SendSms API never worked for their panel; the working
 * flow is UltraFastSend (template based):
 *
 *   POST https://RestfulSms.com/api/UltraFastSend/direct
 *   Body: {
 *     mobile: "09…", UserApiKey: …, SecretKey: …,
 *     TemplateId: …, ParameterArray: [{Parameter, ParameterValue}, …]
 *   }
 *
 * The TemplateId comes ONLY from the SMS-template registry (مرکز پیامک
 * → «کد قرار گرفتن در پنل پیامک») — the per-provider OTP field was removed.
 *
 * Settings: sms.restfulsms.api_key (UserApiKey),
 *           sms.restfulsms.secret_key (SecretKey)
 */
class RestfulSmsDriver implements SmsDriver
{
    public function name(): string
    {
        return 'restfulsms';
    }

    public function label(): string
    {
        return 'sms.ir';
    }

    public function settingsFields(): array
    {
        return [
            'api_key' => ['کلید API (UserApiKey)', true],
            'secret_key' => ['کلید مخفی (SecretKey)', true],
        ];
    }

    public function patternBased(): bool
    {
        return true;
    }

    public function send(SmsMessage $message): SmsResult
    {
        $apiKey = (string) Setting::get('sms.restfulsms.api_key', '');
        $secretKey = (string) Setting::get('sms.restfulsms.secret_key', '');
        $templateId = (string) $message->template;

        if ($apiKey === '' || $secretKey === '') {
            return SmsResult::failure('تنظیمات sms.ir ناقص است (UserApiKey و SecretKey را در تنظیمات پیامک وارد کنید).');
        }

        if ($templateId === '') {
            return SmsResult::failure('شناسه قالب (TemplateId) برای این پیامک تنظیم نشده است — در «قالب‌های پیامک» فیلد «کد قرار گرفتن در پنل پیامک» را پر کنید.');
        }

        try {
            // Proven implementation passes the template variables as a
            // ParameterArray of {Parameter, ParameterValue} pairs.
            $parameters = [];

            foreach ($message->data as $name => $value) {
                $parameters[] = [
                    'Parameter' => (string) $name,
                    'ParameterValue' => (string) $value,
                ];
            }

            $payload = [
                'UserApiKey' => $apiKey,
                'SecretKey' => $secretKey,
                'mobile' => Phone::toLocal($message->to),
                'TemplateId' => $templateId,
                'ParameterArray' => $parameters !== [] ? $parameters : [['Parameter' => 'code', 'ParameterValue' => '']],
            ];

            $response = Http::timeout(15)->post(
                'https://RestfulSms.com/api/UltraFastSend/direct',
                $payload,
            );

            $body = (string) $response->body();
            $json = json_decode($body, true) ?? [];

            if (! $response->successful() || (($json['IsSuccessful'] ?? false) !== true)) {
                return SmsResult::failure(
                    'sms.ir: '.($json['Message'] ?? mb_substr($body, 0, 180) ?: 'خطای ناشناخته'),
                    $json ?: ['raw' => $body],
                );
            }

            return SmsResult::success(
                providerMessageId: (string) (data_get($json, 'Data.PackId') ?? data_get($json, 'Value.PackageId') ?? ''),
                raw: $json,
            );
        } catch (ConnectionException $e) {
            return SmsResult::failure('sms.ir: خطای اتصال — '.$e->getMessage());
        } catch (\Throwable $e) {
            return SmsResult::failure('sms.ir: '.$e->getMessage());
        }
    }
}
