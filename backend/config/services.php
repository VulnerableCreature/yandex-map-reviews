<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'yandex_maps' => [
        'api_base' => env('YANDEX_MAPS_API_BASE', 'https://yandex.ru/maps/api/business'),
        'timeout' => (int) env('YANDEX_MAPS_HTTP_TIMEOUT', 15),

        // Задержка между запросами страниц отзывов одной организации (анти-бан, п.4 ТЗ)
        'throttle_ms' => (int) env('YANDEX_MAPS_THROTTLE_MS', 400),

        // Ротация User-Agent между запросами
        'user_agents' => [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0 Safari/537.36',
        ],

        // Список прокси для ротации (по одному на строку в .env, через запятую) — см. README, п.4.
        'proxies' => env('YANDEX_MAPS_PROXIES', '')
                |> (fn($x) => explode(',', $x))
                |> array_filter(...),
    ],

];
