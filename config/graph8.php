<?php

return [
    'base_url' => env(
        'GRAPH8_BASE_URL',
        'https://be.graph8.com/api/v1'
    ),

    'api_token' => env(
        'GRAPH8_API_TOKEN'
    ),

    'owner_id' => env(
        'GRAPH8_OWNER_ID'
    ),

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
        'secret' => env(
            'GRAPH8_WEBHOOK_SECRET'
        ),

        'signature_header' => env(
            'GRAPH8_WEBHOOK_SIGNATURE_HEADER',
            'X-G8-Signature'
        ),
    ],

    'endpoints' => [
        'events' => env(
            'GRAPH8_EVENTS_ENDPOINT'
        ),

        'companies' => env(
            'GRAPH8_COMPANIES_ENDPOINT',
            '/companies'
        ),

        'contacts' => env(
            'GRAPH8_CONTACTS_ENDPOINT',
            '/contacts'
        ),

        'deals' => env(
            'GRAPH8_DEALS_ENDPOINT',
            '/deals'
        ),

        'actions' => env(
            'GRAPH8_ACTIONS_ENDPOINT'
        ),
    ],
];