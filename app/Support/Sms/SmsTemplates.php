<?php

namespace App\Support\Sms;

use App\Models\SmsTemplate;
use App\Support\SchemaReady;

/**
 * Central SMS-template registry («مرکز پیامک»).
 *
 * Resolves a template from the DB (cached), renders its body by swapping
 * {variables}, attaches the provider pattern code for pattern-based
 * drivers (Kavenegar Verify/Lookup, IPPanel patterns) and falls back to
 * the legacy sms/ blade views when the table has not been seeded yet.
 *
 *   $msg  = SmsTemplates::make('otp_login', $phone, ['code' => '123456', 'minutes' => 5], 'seller');
 *   $res  = app(SmsManager::class)->send($msg);
 *   // or one-liner:
 *   $res  = SmsTemplates::send('otp_login', $phone, ['code' => '123456', 'minutes' => 5]);
 */
class SmsTemplates
{
    /** Cache lifetime for template rows. */
    protected const TTL_SECONDS = 300;

    /** Group registry: slug => [label, icon] (sidebar/filter chips). */
    public const GROUPS = [
        'orders' => ['سفارش‌ها', 'bag'],
        'auth'   => ['ورود و احراز', 'message'],
    ];

    /** Legacy view fallback for keys that predate the templates table. */
    protected const VIEW_FALLBACKS = ['otp_login' => 'otp'];

    /* ─────────────────────────── lookup ─────────────────────────── */

    public static function find(string $key): ?SmsTemplate
    {
        if (! SchemaReady::check('sms_templates')) {
            return null;
        }

        // Cache the RAW attribute strings (never the Eloquent object nor
        // pre-cast values — re-casting already-cast arrays throws).
        $data = cache()->remember(
            'instashop.sms-template.v2.'.$key,
            self::TTL_SECONDS,
            fn () => SmsTemplate::query()->where('key', $key)->first()?->getRawOriginal(),
        );

        if (! is_array($data) || $data === []) {
            return null;
        }

        return (new SmsTemplate)->setRawAttributes($data, true);
    }

    public static function flush(string $key): void
    {
        cache()->forget('instashop.sms-template.v2.'.$key);
    }

    /* ─────────────────────────── rendering ─────────────────────── */

    /** Swap {placeholders} — unknown ones stay untouched. */
    public static function render(?SmsTemplate $template, array $data): string
    {
        if ($template === null) {
            return '';
        }

        $pairs = [];

        foreach ($template->variables ?? [] as $var) {
            $pairs['{'.$var.'}'] = (string) ($data[$var] ?? '{'.$var.'}');
        }

        return strtr($template->body, $pairs);
    }

    /** Stored sample values (merged with app_name default) for previews. */
    public static function sampleData(?SmsTemplate $template): array
    {
        return array_merge(
            ['app_name' => (string) \App\Models\Setting::get('theme.app_name', 'InstaShop')],
            $template->sample_data ?? [],
        );
    }

    /** Live-preview text with sample data. */
    public static function preview(string $key, array $override = []): string
    {
        $template = self::find($key);

        if ($template === null) {
            return '';
        }

        return self::render($template, array_merge(self::sampleData($template), $override));
    }

    /* ─────────────────────────── messages ─────────────────────── */

    /**
     * Build an SmsMessage from a stored template: text body for body-text
     * drivers + pattern code for pattern-driven drivers (set through
     * ->template(); drivers prefer it over the raw body).
     */
    public static function make(string $key, string $to, array $data = [], ?string $panel = null): SmsMessage
    {
        $template = self::find($key);

        $message = SmsMessage::make($to)->panel($panel ?? 'landing');

        // Audit anchor: which template produced this message.
        $message->templateKey = $key;

        if ($template !== null && $template->enabled) {
            $message->text(self::render($template, $data));

            if ((string) $template->pattern_code !== '') {
                $message->template($template->pattern_code);
            }

            return $message;
        }

        // Disabled or missing → legacy view fallback (pre-seed installs).
        if ($template !== null && ! $template->enabled) {
            // Disabled templates suppress body-text output but keep the
            // pattern path so pattern providers keep working if configured.
            if ((string) $template->pattern_code !== '') {
                $message->template($template->pattern_code);

                return $message;
            }

            $message->text('');

            return $message;
        }

        $view = self::VIEW_FALLBACKS[$key] ?? $key;

        if (view()->exists('sms.'.$view)) {
            $message->template($view, $data);
        }

        return $message;
    }

    /** Send through the active driver + audit row, in one call. */
    public static function send(string $key, string $to, array $data = [], string $panel = 'landing', ?string $driver = null): SmsResult
    {
        return app(SmsManager::class)->send(self::make($key, $to, $data, $panel), $driver);
    }
}
