<?php

/*
|--------------------------------------------------------------------------
| InstaShop Installer configuration
|--------------------------------------------------------------------------
| Server requirements & folder permissions rendered by the wizard. Paths
| are resolved at load time (after the framework booted, so base_path()
| and storage_path() are available).
*/

return [

    // Minimum PHP version (mirrors the root composer.json constraint)
    'php_min' => '8.3',

    'requirements' => [

        'required' => [
            'افزونه OpenSSL' => 'openssl',
            'افزونه PDO' => 'PDO',
            'درایور PDO MySQL' => 'pdo_mysql',
            'افزونه Mbstring (یونیکد)' => 'mbstring',
            'افزونه Tokenizer' => 'tokenizer',
            'افزونه XML' => 'xml',
            'افزونه Ctype' => 'ctype',
            'افزونه JSON' => 'json',
            'افزونه Fileinfo' => 'fileinfo',
            'افزونه cURL' => 'curl',
        ],

        // Missing optional items show an amber warning but never block.
        'optional' => [
            'افزونه GD (پردازش تصویر)' => 'gd',
            'افزونه Zip' => 'zip',
            'افزونه Intl (شمسی/اعداد)' => 'intl',
        ],
    ],

    'permissions' => [
        'پوشه اصلی پروژه (ساخت .env)' => base_path(),
        'پوشه storage' => storage_path(),
        'پوشه storage/app' => storage_path('app'),
        'پوشه storage/framework' => storage_path('framework'),
        'پوشه storage/framework/cache' => storage_path('framework/cache'),
        'پوشه storage/framework/sessions' => storage_path('framework/sessions'),
        'پوشه storage/framework/views' => storage_path('framework/views'),
        'پوشه storage/logs' => storage_path('logs'),
        'پوشه bootstrap/cache' => base_path('bootstrap/cache'),
    ],

    // Aggregator seeder is skipped — every real seeder runs individually.
    'excluded_seeders' => ['DatabaseSeeder'],

];
