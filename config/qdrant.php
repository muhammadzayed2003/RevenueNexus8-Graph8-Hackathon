<?php

return [
    'url' => env('QDRANT_URL'),

    'api_key' => env('QDRANT_API_KEY'),

    'collection' => env(
        'QDRANT_COLLECTION',
        'revenue_nexus_knowledge'
    ),

    'embedding_model' => env(
        'GEMINI_EMBEDDING_MODEL',
        'gemini-embedding-001'
    ),
];