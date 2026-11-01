<?php

namespace App\Support\Sms;

/**
 * Value object describing an outgoing SMS.
 * Carries either a plain text body or a template + data pair —
 * verify/lookup providers (Kavenegar, IPPanel patterns) use templates,
 * body-text providers (MelliPayamak, RestfulSms) use text.
 */
class SmsMessage
{
    /** Audit anchor: which SMS template produced this message (sms_templates.key). */
    public ?string $templateKey = null;

    public function __construct(
        public string $to,
        public ?string $text = null,
        public ?string $template = null,
        public array $data = [],
        public string $panel = 'landing',
    ) {}

    public static function make(string $to): static
    {
        return new static(to: $to);
    }

    public function text(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function template(string $template, array $data = []): static
    {
        $this->template = $template;
        $this->data     = $data;

        return $this;
    }

    public function panel(string $panel): static
    {
        $this->panel = $panel;

        return $this;
    }

    /** Human-readable body (used for logging / body-text providers). */
    public function body(): string
    {
        if ($this->text !== null && $this->text !== '') {
            return $this->text;
        }

        return view('sms.'.$this->template, $this->data)->render();
    }
}
