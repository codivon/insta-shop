<?php

namespace App\Support\Sms;

/**
 * Value object returned by every SMS driver.
 */
class SmsResult
{
    public function __construct(
        public bool $ok,
        public ?string $providerMessageId = null,
        public ?string $error = null,
        public array $raw = [],
    ) {}

    public static function success(?string $providerMessageId = null, array $raw = []): static
    {
        return new static(ok: true, providerMessageId: $providerMessageId, raw: $raw);
    }

    public static function failure(string $error, array $raw = []): static
    {
        return new static(ok: false, error: $error, raw: $raw);
    }
}
