<?php

use Maize\LegalConsent\DefaultLegalDocumentFinder;
use Maize\LegalConsent\Models\LegalConsent;
use Maize\LegalConsent\Models\LegalDocument;

return [
    /*
    |--------------------------------------------------------------------------
    | Legal document model
    |--------------------------------------------------------------------------
    |
    | Here you may specify the fully qualified class name of the legal document model.
    |
    */

    'legal_document_model' => LegalDocument::class,

    /*
    |--------------------------------------------------------------------------
    | Legal consent model
    |--------------------------------------------------------------------------
    |
    | Here you may specify the fully qualified class name of the legal consent model.
    |
    */

    'legal_consent_model' => LegalConsent::class,

    /*
    |--------------------------------------------------------------------------
    | Legal document finder
    |--------------------------------------------------------------------------
    |
    | Here you may specify the fully qualified class name of the legal document finder class.
    |
    */

    'legal_document_finder' => DefaultLegalDocumentFinder::class,

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
            'withdraw' => [
                'middleware' => ['auth:api'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto accept on registration
    |--------------------------------------------------------------------------
    |
    | When enabled, the package listens to the framework's Registered event and
    | automatically accepts every allowed document type whose consent value is
    | present in the request (e.g. "privacy-policy_accepted"). Disabled by
    | default: opt in only if you want registration to record consent.
    |
    */

    'auto_accept_on_registered' => false,

    /*
    |--------------------------------------------------------------------------
    | Encrypt audit metadata
    |--------------------------------------------------------------------------
    |
    | The IP address and user agent stored alongside each consent are personal
    | data (GDPR art. 32). When enabled, they are encrypted at rest using the
    | application key. Disable only if you need to query these columns in clear
    | text (note: doing so stores personal data unencrypted).
    |
    */

    'encrypt_audit_metadata' => true,

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
