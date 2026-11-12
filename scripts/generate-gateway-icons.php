<?php

/*
|--------------------------------------------------------------------------
| Gateway brand icon generator (v0.29.0)
|--------------------------------------------------------------------------
|
| Writes one bundled brand tile per payment gateway into
| public/images/gateways/{slug}.svg — the DEFAULT icon the admin panel
| and the checkout page show for every gateway (client request: «همه
| درگاه‌های پرداخت تصویر یا آیکون مخصوص داشته باشند و قابل تغییر باشند،
| پیش‌فرض هم آیکون خود درگاه‌ها باشد»).
|
| The admin can override any gateway's icon with a custom PNG/JPG/WebP
| upload (see App\Support\Payments\GatewayIcons + PaymentSettings);
| these SVGs stay the built-in fallback that always exists.
|
| Run:  php scripts/generate-gateway-icons.php
|
*/

$brands = [
    // slug            from        to         mono            code            glyph-type
    'local' => ['#64748B', '#334155', null, 'TEST', 'card'],
    'zarinpal' => ['#FFD34E', '#E8A100', 'Z', 'ZARINPAL', 'text'],
    'zibal' => ['#A855F7', '#6B21A8', 'ژ', 'ZIBAL', 'text'],
    'novinopay' => ['#34D399', '#047857', 'N', 'NOVINOPAY', 'text'],
    'payping' => ['#F87171', '#DC2626', 'پ', 'PAYPING', 'text'],
    'sadad' => ['#38BDF8', '#0369A1', 'س', 'SADAD', 'text'],
    'behpardakht' => ['#60A5FA', '#1D4ED8', 'ب', 'BEHPARDAKHT', 'text'],
    'parsian' => ['#FB923C', '#C2410C', 'پ', 'PARSIAN', 'text'],
    'irankish' => ['#FBBF24', '#C2410C', 'ک', 'IRANKISH', 'text'],
    'pasargad' => ['#E879F9', '#7E22CE', 'پ', 'PASARGAD', 'text'],
    'asanpardakht' => ['#A3E635', '#15803D', 'آ', 'ASANPARDAKHT', 'text'],
    'sepehr' => ['#22D3EE', '#0F766E', 'س', 'SEPEHR', 'text'],
    'sep' => ['#10B981', '#115E59', 'سپ', 'SAMAN SEP', 'text-wide'],
    'saman' => ['#3B82F6', '#1E3A8A', 'س', 'SAMAN', 'text'],
    'idpay' => ['#F472B6', '#BE123C', 'iD', 'IDPAY', 'text'],
    'payir' => ['#2DD4BF', '#0F766E', 'P', 'PAY.IR', 'text'],
    'toman' => ['#F59E0B', '#1E293B', 'ت', 'TOMAN', 'text'],
];

$dir = __DIR__.'/../public/images/gateways';

if (! is_dir($dir) && ! mkdir($dir, 0775, true)) {
    fwrite(STDERR, "Cannot create {$dir}\n");
    exit(1);
}

/**
 * Monogram <text> — Persian marks ride the OS font stack (Vazirmatn →
 * Tahoma → Arabic-script fallback); latin marks always render cleanly.
 * Persian text must NOT get letter-spacing (it would break the joining).
 */
function glyph(string $mono, string $type): string
{
    if ($type === 'card') {
        // The test gateway — a plain bank-card outline.
        return <<<'SVG'
    <rect x="34" y="44" width="60" height="42" rx="9" fill="none" stroke="#ffffff" stroke-width="6"/>
    <path d="M34 58h60" stroke="#ffffff" stroke-width="6"/>
SVG;
    }

    $size = $type === 'text-wide' ? 46 : 56;

    return sprintf(
        '    <text x="64" y="82" text-anchor="middle" font-family="Vazirmatn, Segoe UI, Tahoma, Arial, sans-serif" font-size="%d" font-weight="800" fill="#ffffff">%s</text>',
        $size,
        htmlspecialchars($mono, ENT_XML1),
    );
}

$written = [];

foreach ($brands as $slug => [$from, $to, $mono, $code, $type]) {
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128" width="128" height="128" role="img" aria-label="{$slug}">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="{$from}"/>
      <stop offset="1" stop-color="{$to}"/>
    </linearGradient>
  </defs>
  <rect width="128" height="128" rx="30" fill="url(#g)"/>
  <circle cx="106" cy="20" r="42" fill="#ffffff" opacity="0.10"/>
  <circle cx="14" cy="114" r="46" fill="#000000" opacity="0.10"/>

SVG
        .glyph((string) $mono, $type)
        .<<<'SVG'

  <text x="64" y="107" text-anchor="middle" font-family="ui-monospace, SFMono-Regular, Menlo, Consolas, monospace" font-size="12" font-weight="700" letter-spacing="2" fill="#ffffff" opacity="0.8">CODE</text>
</svg>

SVG;

    $svg = str_replace('>CODE</text>', '>'.htmlspecialchars($code, ENT_XML1).'</text>', $svg);

    $target = "{$dir}/{$slug}.svg";
    file_put_contents($target, $svg);
    $written[] = [$slug, filesize($target)];
}

echo 'Gateway icons written to public/images/gateways:'.PHP_EOL;

foreach ($written as [$slug, $bytes]) {
    echo sprintf('  %-14s %5d bytes%s', $slug, $bytes, PHP_EOL);
}

echo count($written).' icons total.'.PHP_EOL;
