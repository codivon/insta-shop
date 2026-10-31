<?php

/*
|--------------------------------------------------------------------------
| InstaShop Panels Registry
|--------------------------------------------------------------------------
|
| Central definition of the three application panels. Every panel has its
| own auth guard, session table and cookie so logins never interfere with
| each other. The ResolvePanel middleware reads this config per request
| (based on the URI prefix) BEFORE the session starts.
|
*/

return [

    'default' => 'landing',

    'panels' => [

        'landing' => [
            'label'        => 'Landing',
            'prefix'       => null,               // everything else
            'guard'        => null,               // public area
            'session'      => [
                'cookie' => 'instashop_landing_session',
                'table'  => 'sessions',
                'lifetime' => 120,
            ],
            'colors'       => [
                'bg'    => 'bg-slate-50',
                'accent'=> 'from-primary-600 to-primary-800',
            ],
        ],

        'admin' => [
            'label'        => 'پنل مدیریت',
            'prefix'       => 'admin',
            'guard'        => 'admin',
            'layout'       => 'admin',
            'session'      => [
                'cookie' => 'instashop_admin_session',
                'table'  => 'admin_sessions',
                'lifetime' => 480,
            ],
        ],

        'seller' => [
            'label'        => 'پنل فروشنده',
            'prefix'       => 'seller',
            'guard'        => 'seller',
            'layout'       => 'seller',
            'session'      => [
                'cookie' => 'instashop_seller_session',
                'table'  => 'seller_sessions',
                'lifetime' => 1440,
            ],
        ],

        'customer' => [
            'label'        => 'فروشگاه (اپ مشتری)',
            'prefix'       => 'shop',
            'guard'        => 'customer',
            'layout'       => 'customer',
            'session'      => [
                'cookie' => 'instashop_customer_session',
                'table'  => 'customer_sessions',
                'lifetime' => 43200,
            ],
        ],
    ],

];
