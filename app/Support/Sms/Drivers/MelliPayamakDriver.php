<?php

namespace App\Support\Sms\Drivers;

use App\Support\Phone;
use App\Support\Sms\Contracts\SmsDriver;
use App\Support\Sms\SmsMessage;
use App\Support\Sms\SmsResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * MelliPayamak — plain body-text API.
 * Docs: https://rest.payamak-panel.com/api/SendSMS/SendSMS
 * Settings: sms.mellipayamak.username, sms.mellipayamak.password, sms.mellipayamak.sender
 */
class MelliPayamakDriver implements SmsDriver
{
    public function name(): string
    {
        return 'mellipayamak';
    }

    public function label(): string
    {
        return 'ملی‌پیامک (متن آزاد / BodyText)';
    }

    public function settingsFields(): array
    {
        return [
            'username' => ['نام کاربری پنل', true],
            'password' => ['رمز عبور پنل', true],
            'sender'   => ['خط ارسال (شماره خط)', true],
        ];
    }

    public function send(SmsMessage $message): SmsResult
    {
        $username = (string) \App\Models\Setting::get('sms.mellipayamak.username', '');
        $password = (string) \App\Models\Setting::get('sms.mellipayamak.password', '');
        $sender   = (string) \App\Models\Setting::get('sms.mellipayamak.sender', '');

        if ($username === '' || $password === '' || $sender === '') {
            return SmsResult::failure('تنظیمات ملی‌پیامک ناقص است.');
        }

        try {
            $response = Http::timeout(15)->asForm()->post(
                'https://rest.payamak-panel.com/api/SendSMS/SendSMS',
                [
                    'username' => $username,
                    'password' => $password,
                    'to'       => Phone::toLocal($message->to),
                    'from'     => $sender,
                    'text'     => $message->body(),
                    'isFlash'  => 'false',
                ],
            );

            $json = $response->json() ?? [];

            if (! $response->successful() || (($json['RetStatus'] ?? null) != 1 || strtoupper((string) ($json['StrRetStatus'] ?? '')) !== 'OK')) {
                return SmsResult::failure(
                    'ملی‌پیامک: '.($json['Message'] ?? 'خطای ناشناخته (RetStatus='.($json['RetStatus'] ?? '?').')'),
                    $json,
                );
            }

            return SmsResult::success(
                providerMessageId: (string) ($json['Value'] ?? ''),
                raw: $json,
            );
        } catch (ConnectionException $e) {
            return SmsResult::failure('ملی‌پیامک: خطای اتصال — '.$e->getMessage());
        } catch (\Throwable $e) {
            return SmsResult::failure('ملی‌پیامک: '.$e->getMessage());
        }
    }
}
