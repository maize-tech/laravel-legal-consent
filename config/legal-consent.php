<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Legal document model
    |--------------------------------------------------------------------------
    |
    | Here you may specify the fully qualified class name of the legal document model.
    |
    */

    'legal_document_model' => Maize\LegalConsent\Models\LegalDocument::class,

    /*
    |--------------------------------------------------------------------------
    | Legal consent model
    |--------------------------------------------------------------------------
    |
    | Here you may specify the fully qualified class name of the legal consent model.
    |
    */

    'legal_consent_model' => Maize\LegalConsent\Models\LegalConsent::class,

    /*
    |--------------------------------------------------------------------------
    | Legal document finder
    |--------------------------------------------------------------------------
    |
    | Here you may specify the fully qualified class name of the legal document finder class.
    |
    */

    'legal_document_finder' => Maize\LegalConsent\DefaultLegalDocumentFinder::class,

    /*
    |--------------------------------------------------------------------------
    | Route configurations
    |--------------------------------------------------------------------------
    |
    | Here you may specify whether routes should be enabled or not.
    | You can also customize the routes prefix and middlewares.
    |
    */

    'routes' => [
        'enabled' => true,
        'prefix' => 'legal',
        'name' => 'legal',
        'middleware' => ['api'],
        'endpoints' => [
            'show' => [
                'middleware' => [],
            ],
            'consent' => [
                'middleware' => ['auth:api'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed document types
    |--------------------------------------------------------------------------
    |
    | Here you may specify the list of accepted legal document types
    | for all requests.
    |
    */

    'allowed_document_types' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed acceptable values
    |--------------------------------------------------------------------------
    |
    | Here you may specify the list of accepted values for each legal document
    | consent request.
    |
    */

    'allowed_acceptable_values' => [
        'yes',
        'on',
        '1',
        1,
        true,
        'true',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Here you may specify the amount of time, in seconds, where each legal
    | document is cached to avoid multiple database queries.
    |
    */

    'cache' => [
        'document_ttl' => 3600,
        'document_user_ttl' => 3600,
    ],
];
