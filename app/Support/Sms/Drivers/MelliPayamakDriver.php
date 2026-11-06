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
 * MelliPayamak — ملی‌پیامک.
 *
 * v0.18.1 ported from the customer's proven implementation:
 * when the message carries a pattern/bodyId (from the SMS-template
 * registry) it goes through BaseServiceNumber (sendByBaseNumber —
 * ordered segments joined with «;»), otherwise it falls back to the
 * plain free-text SendSMS API.
 *
 * REST equivalents (no SDK dependency):
 *   - POST https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber
 *     (username, password, text, to, bodyId)
 *   - POST https://rest.payamak-panel.com/api/SendSMS/SendSMS
 *     (username, password, to, from, text, isFlash)
 *
 * Settings: sms.mellipayamak.username, sms.mellipayamak.password,
 *           sms.mellipayamak.sender (free-text only)
 */
class MelliPayamakDriver implements SmsDriver
{
    public function name(): string
    {
        return 'mellipayamak';
    }

    public function label(): string
    {
        return 'ملی‌پیامک (پترن BaseServiceNumber + متن آزاد)';
    }

    public function settingsFields(): array
    {
        return [
            'username' => ['نام کاربری پنل', true],
            'password' => ['رمز عبور پنل', true],
            'sender' => ['خط ارسال (فقط برای متن آزاد)', false],
        ];
    }

    public function patternBased(): bool
    {
        return false;
    }

    public function send(SmsMessage $message): SmsResult
    {
        $username = (string) Setting::get('sms.mellipayamak.username', '');
        $password = (string) Setting::get('sms.mellipayamak.password', '');
        $sender = (string) Setting::get('sms.mellipayamak.sender', '');
        $bodyId = (string) $message->template;
        $to = Phone::toLocal($message->to);

        if ($username === '' || $password === '') {
            return SmsResult::failure('تنظیمات ملی‌پیامک ناقص است (نام کاربری و رمز عبور را در تنظیمات پیامک وارد کنید).');
        }

        try {
            if ($bodyId !== '') {
                // Pattern mode — proven sendByBaseNumber flow: the segment
                // values joined with «;» in template-variable order.
                $segments = array_values($message->data);

                if ($segments === []) {
                    return SmsResult::failure('ملی‌پیامک: پترن (BodyId='.$bodyId.') بدون مقدار متغیر رها شده است.');
                }

                $response = Http::timeout(15)->asForm()->post(
                    'https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber',
                    [
                        'username' => $username,
                        'password' => $password,
                        'text' => implode(';', $segments),
                        'to' => $to,
                        'bodyId' => $bodyId,
                    ],
                );
            } else {
                // Free-text fallback.
                if ($sender === '') {
                    return SmsResult::failure('ملی‌پیامک: برای ارسال متن آزاد، «خط ارسال» را در تنظیمات پیامک وارد کنید (یا کد پترن قالب را تنظیم کنید).');
                }

                $response = Http::timeout(15)->asForm()->post(
                    'https://rest.payamak-panel.com/api/SendSMS/SendSMS',
                    [
                        'username' => $username,
                        'password' => $password,
                        'to' => $to,
                        'from' => $sender,
                        'text' => $message->body(),
                        'isFlash' => 'false',
                    ],
                );
            }

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
