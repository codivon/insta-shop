<?php

namespace App\Support\Sms\Contracts;

use App\Support\Sms\SmsMessage;
use App\Support\Sms\SmsResult;

/**
 * Contract every SMS driver must fulfil.
 *
 * Drivers are resolved by name from the DB-driven settings (group "sms"),
 * so new Iranian providers can be plugged in without touching business code.
 */
interface SmsDriver
{
    /** Machine name used in settings + sms_logs.driver */
    public function name(): string;

    /** Persian label shown in the admin panel */
    public function label(): string;

    /** Settings fields this driver needs: name => [label, required] */
    public function settingsFields(): array;

    /** Send the message. Must never throw — failures go into SmsResult. */
    public function send(SmsMessage $message): SmsResult;
}
