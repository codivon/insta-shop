<?php

/*
|--------------------------------------------------------------------------
| InstaShop payment gateways (shetabit/payment)
|--------------------------------------------------------------------------
|
| The active gateway is NOT fixed here — it is read at runtime from the
| database settings (group "payment", key "gateway") via
| App\Support\Payments\PaymentService::activeGateway(), so the admin
| panel can switch gateways later (Phase 6).
|
| Default: the "local" test gateway — a mock gateway page that needs no
| real bank account (success + cancel buttons) for end-to-end testing.
|
| Per-driver blocks only contain overrides; anything missing is merged
| from the shetabit/multipay defaults at runtime.
|
*/

return [

    'default' => env('PAYMENT_DRIVER', 'local'),

    'drivers' => [

        'local' => [
            'callbackUrl' => '/shop/payment/callback',
            'title' => 'درگاه پرداخت آزمایشی',
            'description' => 'این درگاه صرفاً برای تست روند خرید است و پول واقعی جابه‌جا نمی‌شود.',
            'orderLabel' => 'شماره سفارش',
            'amountLabel' => 'مبلغ قابل پرداخت',
            'payButton' => 'پرداخت موفق (تست)',
            'cancelButton' => 'انصراف / پرداخت ناموفق',
        ],

        'zarinpal' => [
            'merchantId' => env('ZARINPAL_MERCHANT_ID', ''),
            'callbackUrl' => '/shop/payment/callback',
            'description' => 'خرید از InstaShop',
        ],

        'zibal' => [
            'merchantId' => env('ZIBAL_MERCHANT_ID', ''),
            'callbackUrl' => '/shop/payment/callback',
            'description' => 'خرید از InstaShop',
        ],

        'behpardakht' => [
            'terminalId' => env('BEHPARDAKHT_TERMINAL_ID', ''),
            'username' => env('BEHPARDAKHT_USERNAME', ''),
            'password' => env('BEHPARDAKHT_PASSWORD', ''),
            'callbackUrl' => '/shop/payment/callback',
            'description' => 'خرید از InstaShop',
        ],

        'novinopay' => [
            'merchantId' => env('NOVINOPAY_MERCHANT_ID', ''),
            'callbackUrl' => '/shop/payment/callback',
            'description' => 'خرید از InstaShop',
        ],

        'payping' => [
            'merchantId' => env('PAYPING_MERCHANT_ID', ''),
            'callbackUrl' => '/shop/payment/callback',
            'description' => 'خرید از InstaShop',
        ],

        'sadad' => [
            'key' => env('SADAD_KEY', ''),
            'merchantId' => env('SADAD_MERCHANT_ID', ''),
            'terminalId' => env('SADAD_TERMINAL_ID', ''),
            'callbackUrl' => '/shop/payment/callback',
            'description' => 'خرید از InstaShop',
        ],

        'parsian' => [
            'merchantId' => env('PARSIAN_MERCHANT_ID', ''),
            'callbackUrl' => '/shop/payment/callback',
            'description' => 'خرید از InstaShop',
        ],

        'irankish' => [
            'terminalId' => env('IRANKISH_TERMINAL_ID', ''),
            'password' => env('IRANKISH_PASSWORD', ''),
            'acceptorId' => env('IRANKISH_ACCEPTOR_ID', ''),
            'pubKey' => env('IRANKISH_PUBLIC_KEY', ''),
            'callbackUrl' => '/shop/payment/callback',
            'description' => 'خرید از InstaShop',
        ],

        'pasargad' => [
            'userName' => env('PASARGAD_USERNAME', ''),
            'password' => env('PASARGAD_PASSWORD', ''),
            'terminalCode' => env('PASARGAD_TERMINAL_CODE', ''),
            'callbackUrl' => '/shop/payment/callback',
            'description' => 'خرید از InstaShop',
        ],

        'asanpardakht' => [
            'username' => env('ASANPARDAKHT_USERNAME', ''),
            'password' => env('ASANPARDAKHT_PASSWORD', ''),
            'merchantConfigID' => env('ASANPARDAKHT_MERCHANT_CONFIG_ID', ''),
            'callbackUrl' => '/shop/payment/callback',
            'description' => 'خرید از InstaShop',
        ],

        'sepehr' => [
            'terminalId' => env('SEPEHR_TERMINAL_ID', ''),
            'callbackUrl' => '/shop/payment/callback',
            'description' => 'خرید از InstaShop',
        ],

        'sep' => [
            'terminalId' => env('SEP_TERMINAL_ID', ''),
            'callbackUrl' => '/shop/payment/callback',
            'description' => 'خرید از InstaShop',
        ],

    ],

];
