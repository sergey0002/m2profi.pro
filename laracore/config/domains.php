<?php

return [
    'app_domain' => get_setting('main', 'app_domain', env('APP_DOMAIN', 'm2profi.pro')),
    'public_domain' => get_setting('main', 'public_domain', env('PUBLIC_DOMAIN', 'm2profi.pro')),
    'app_url' => get_setting('main', 'app_url', env('APP_URL', 'https://m2profi.pro')),
    'public_url' => get_setting('main', 'public_url', env('PUBLIC_URL', 'https://m2profi.pro')),
    'platform_domain' => get_setting('main', 'platform_domain', env('PLATFORM_DOMAIN', 'panel.m2profi.pro.test')),
    
    'paths' => [
        'ajax_router' => '/sahmatka/ajax_router.php',
        'ajax_actions' => '/sahmatka/ajax_actions.php',
        'feed_domclick' => '/sahmatka/domclick-:home_id.xml',
        'feed_yandex' => '/sahmatka/yandex_feedx.php',
        'feed_avito' => '/sahmatka/avito_feedx.php',
        'pbplans' => '/sahmatka/pbplans',
    ],
    
    'emails' => [
        'support' => get_setting('main', 'support_email', env('SUPPORT_EMAIL', 'op-an@em-nsk.group')),
        'noreply' => get_setting('main', 'noreply_email', env('NOREPLY_EMAIL', 'noreply@em-nsk.ru')),
    ],
];
