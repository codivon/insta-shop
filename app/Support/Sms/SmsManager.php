<?php

namespace App\Support\Sms;

use App\Models\Setting;
use App\Models\SmsLog;
use App\Support\Sms\Contracts\SmsDriver;
use App\Support\Sms\Drivers\FarazIPPanelDriver;
use App\Support\Sms\Drivers\FarazSmsDriver;
use App\Support\Sms\Drivers\KavenegarDriver;
use App\Support\Sms\Drivers\LogDriver;
use App\Support\Sms\Drivers\MelliPayamakDriver;
use App\Support\Sms\Drivers\RestfulSmsDriver;

/**
 * Central SMS gateway: resolves the active driver from DB settings
 * (group "sms" — editable from the admin panel) and writes an audit
 * row into sms_logs for every send attempt.
 */
class SmsManager
{
    /** @var array<string, SmsDriver> */
    protected array $drivers = [];

    public function __construct()
    {
        foreach ([
            new LogDriver,
            new KavenegarDriver,
            new FarazIPPanelDriver,
            new FarazSmsDriver,
            new MelliPayamakDriver,
            new RestfulSmsDriver,
        ] as $driver) {
            $this->drivers[$driver->name()] = $driver;
        }
    }

    /** All registered drivers (for the admin settings UI). */
    public function drivers(): array
    {
        return $this->drivers;
    }

    public function has(string $name): bool
    {
        return isset($this->drivers[$name]);
    }

    /** Active driver name from settings (fallback: log). */
    public function activeDriverName(): string
    {
        $name = (string) Setting::get('sms.driver', 'log');

        return $this->has($name) ? $name : 'log';
    }

    public function driver(?string $name = null): SmsDriver
    {
        $name ??= $this->activeDriverName();

        return $this->drivers[$this->has($name) ? $name : 'log'];
    }

    /** Send through the active driver + persist an sms_logs audit row. */
    public function send(SmsMessage $message, ?string $driverName = null): SmsResult
    {
        $driver = $this->driver($driverName);

        try {
            $result = $driver->send($message);
        } catch (\Throwable $e) {
            $result = SmsResult::failure($e->getMessage());
        }

        /*
         * SECURITY: when a REAL provider delivers an OTP, the audit row must
         * not contain the live code — a database/log leak would otherwise
         * expose usable one-time passwords. The test/log driver (local dev)
         * keeps the full body so codes stay visible where they are sent.
         */
        $body = $message->body();

        if ($message->templateKey === 'otp_login' && $driver->name() !== 'log') {
            $body = (string) preg_replace('/\b\d{6}\b/', '●●●●●●', $body);
        }

        SmsLog::create([
            'panel' => $message->panel,
            'driver' => $driver->name(),
            'phone' => $message->to,
            'template' => $message->templateKey ?? $message->template,
            'message' => $body,
            'status' => $result->ok ? 'sent' : 'failed',
            'error' => $result->error,
            'provider_message_id' => $result->providerMessageId,
            'meta' => $result->raw ?: null,
        ]);

        return $result;
    }

    /** Convenience: send a raw text message. */
    public function sendText(string $phone, string $text, string $panel = 'landing'): SmsResult
    {
        return $this->send(SmsMessage::make($phone)->text($text)->panel($panel));
    }
}
