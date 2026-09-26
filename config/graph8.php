<?php

return [
    'base_url' => env('GRAPH8_BASE_URL'),

    'api_token' => env('GRAPH8_API_TOKEN'),

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
        60
    ),

    'webhook' => [
        'secret' => env('GRAPH8_WEBHOOK_SECRET'),

        'signature_header' => env(
            'GRAPH8_WEBHOOK_SIGNATURE_HEADER',
            'X-Graph8-Signature'
        ),
    ],

    'endpoints' => [
        'events' => env('GRAPH8_EVENTS_ENDPOINT'),
        'companies' => env('GRAPH8_COMPANIES_ENDPOINT'),
        'contacts' => env('GRAPH8_CONTACTS_ENDPOINT'),
        'deals' => env('GRAPH8_DEALS_ENDPOINT'),
        'actions' => env('GRAPH8_ACTIONS_ENDPOINT'),
    ],
];