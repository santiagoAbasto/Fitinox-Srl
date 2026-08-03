<?php

return [
    'enabled' => env('ANALYTICS_ENABLED', true),
    'retention_days' => (int) env('ANALYTICS_RETENTION_DAYS', 365),
    'session_minutes' => (int) env('ANALYTICS_SESSION_MINUTES', 30),
    'hash_key' => env('ANALYTICS_HASH_KEY', env('APP_KEY')),
    'exclude_paths' => array_filter(array_map('trim', explode(',', env('ANALYTICS_EXCLUDE_PATHS', '/admin,/clientes')))),
    'allowed_events' => [
        'page_view', 'section_view', 'scroll_depth', 'cta_click', 'whatsapp_click',
        'telegram_click', 'phone_click', 'email_click', 'product_view', 'product_search',
        'add_to_cart', 'remove_from_cart', 'checkout_started', 'quote_generated',
        'form_started', 'form_submitted', 'form_error', 'login_success', 'login_failed',
        'web_vital',
    ],
    'posthog' => [
        'key' => env('VITE_POSTHOG_KEY'),
        'host' => env('VITE_POSTHOG_HOST', 'https://us.i.posthog.com'),
        'recordings' => env('VITE_POSTHOG_RECORDINGS', false),
    ],
    'maxmind_database' => env('MAXMIND_DATABASE_PATH', storage_path('app/geoip/GeoLite2-City.mmdb')),
];
