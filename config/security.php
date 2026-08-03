<?php

return [
    'hsts' => [
        'enabled' => env('SECURITY_HSTS_ENABLED', false),
        'max_age' => env('SECURITY_HSTS_MAX_AGE', 31536000),
        'include_subdomains' => env('SECURITY_HSTS_INCLUDE_SUBDOMAINS', true),
        'preload' => env('SECURITY_HSTS_PRELOAD', false),
    ],
    'csp' => [
        'enforce' => env('SECURITY_CSP_ENFORCE', false),
        'directives' => [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "script-src 'self' 'unsafe-inline' https://challenges.cloudflare.com https://us.i.posthog.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' data: https://fonts.gstatic.com",
            "img-src 'self' data: blob: https:",
            "media-src 'self' blob:",
            "connect-src 'self' https://challenges.cloudflare.com https://*.posthog.com wss:",
            "frame-src 'self' https://challenges.cloudflare.com https://www.youtube.com https://www.youtube-nocookie.com",
            "worker-src 'self' blob:",
            'upgrade-insecure-requests',
        ],
    ],
    'turnstile' => [
        'required_in_production' => env('TURNSTILE_REQUIRED_IN_PRODUCTION', true),
    ],
];
