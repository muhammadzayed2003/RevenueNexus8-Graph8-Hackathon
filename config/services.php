<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file stores third-party service configuration. Credentials remain
    | inside the local environment file and are never committed to Git.
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
            'bot_user_oauth_token' => env(
                'SLACK_BOT_USER_OAUTH_TOKEN'
            ),
            'channel' => env(
                'SLACK_BOT_USER_DEFAULT_CHANNEL'
            ),
        ],
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env(
            'GEMINI_MODEL',
            'gemini-3.1-flash-lite'
        ),
    ],

    'graph8' => [
        'base_url' => env(
            'GRAPH8_BASE_URL',
            'https://be.graph8.com/api/v1'
        ),
        'token' => env('GRAPH8_API_TOKEN'),
        'auth_header' => env(
            'GRAPH8_AUTH_HEADER',
            'Authorization'
        ),
        'auth_scheme' => env(
            'GRAPH8_AUTH_SCHEME',
            'Bearer'
        ),
        'timeout' => (int) env(
            'GRAPH8_TIMEOUT',
            30
        ),
        'companies_endpoint' => env(
            'GRAPH8_COMPANIES_ENDPOINT',
            '/companies'
        ),
        'contacts_endpoint' => env(
            'GRAPH8_CONTACTS_ENDPOINT',
            '/contacts'
        ),
        'deals_endpoint' => env(
            'GRAPH8_DEALS_ENDPOINT',
            '/deals'
        ),
        'events_endpoint' => env(
            'GRAPH8_EVENTS_ENDPOINT'
        ),
        'actions_endpoint' => env(
            'GRAPH8_ACTIONS_ENDPOINT'
        ),
        'webhook_secret' => env(
            'GRAPH8_WEBHOOK_SECRET'
        ),
        'webhook_signature_header' => env(
            'GRAPH8_WEBHOOK_SIGNATURE_HEADER',
            'X-G8-Signature'
        ),
    ],

];