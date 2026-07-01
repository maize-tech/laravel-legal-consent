<p align="center">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="/art/socialcard-dark.png">
  <source media="(prefers-color-scheme: light)" srcset="/art/socialcard-light.png">
  <img src="/art/socialcard-light.png" alt="Social Card of Laravel Legal Consent">
</picture>
</p>

# Laravel Legal Consent

[![Latest Version on Packagist](https://img.shields.io/packagist/v/maize-tech/laravel-legal-consent.svg?style=flat-square)](https://packagist.org/packages/maize-tech/laravel-legal-consent)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/maize-tech/laravel-legal-consent/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/maize-tech/laravel-legal-consent/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/maize-tech/laravel-legal-consent/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/maize-tech/laravel-legal-consent/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/maize-tech/laravel-legal-consent.svg?style=flat-square)](https://packagist.org/packages/maize-tech/laravel-legal-consent)

Easily integrate legal documents (like privacy policies, terms of use, etc.) to your application.

## Installation

You can install the package via composer:

```bash
composer require maize-tech/laravel-legal-consent
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="legal-consent-migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --tag="legal-consent-config"
```

This is the content of the published config file:

```php
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
    | present in the request. Disabled by default.
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
    | application key.
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
```

## Usage

### Basic

To use the package, add the `Maize\LegalConsent\HasLegalConsent` trait and implement the
`Maize\LegalConsent\Contracts\LegalConsenter` contract on all the Authenticatable models you
want to handle. The trait provides every method required by the contract.

Here's an example including the `HasLegalConsent` trait on both User and Admin models:

``` php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Maize\LegalConsent\Contracts\LegalConsenter;
use Maize\LegalConsent\HasLegalConsent;

class User extends Authenticatable implements LegalConsenter
{
    use HasLegalConsent;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
    ];
}
```

``` php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Maize\LegalConsent\Contracts\LegalConsenter;
use Maize\LegalConsent\HasLegalConsent;

class Admin extends Authenticatable implements LegalConsenter
{
    use HasLegalConsent;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
    ];
}
```

Once done, you must define the list of allowed document types by adding them in the `allowed_document_types` list from `config/legal-consent.php`.

``` php
'allowed_document_types' => [
    'privacy-policy',
    'terms-of-use',
],
```

You can then create one or multiple documents from the DB or, if you wish, you could handle the creation with a CMS.

Here are the fields you should fill:
- **type**: the document type name
- **status**: the document lifecycle status (`draft`, `published` or `archived`). Defaults to `draft`; only `published` documents are served by the default finder
- **body**: the content of the document
- **notes**: additional notes to show for the document (optional)
- **published_at**: the date of publication for the given document

The following fields are managed automatically and should not be set manually:
- **version**: an incremental version number, assigned per `type` on creation
- **content_hash**: a `sha256` hash of the `body`, recomputed whenever the document is saved. It is the integrity anchor used for versioning (see below)

Let's say we create a privacy policy document with the publication on 2021-01-01: here's the model entity we would have:

``` php
$legalDocument = [
    "id" => 1,
    "type" => "privacy-policy",
    "version" => 1,
    "status" => "published",
    "body" => "The privacy policy's very long text",
    "notes" => null,
    "content_hash" => "9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08",
    "published_at" => "2021-01-01",
    "updated_at" => "2021-01-01",
    "created_at" => "2021-01-01",
];
```

You can now call the custom API to retrieve and accept the current document, which can be customized in `config/legal-consent.php`:

#### GET - **/legal/documents/privacy-policy**

This endpoint retrieves the current legal document using the given criteria:
- the document type must be `privacy-policy`
- the published_at date must be earlier than `now()`

The document entries are then ordered by their published_at date in order to pick the latest one published.

The response contains the document id (used for the POST route) along with all its information useful for rendering.
Here is a sample response body:

``` json
{
    "data": {
        "id": 1,
        "type": "privacy-policy",
        "version": 1,
        "status": "published",
        "body": "The privacy policy's very long text",
        "notes": null,
        "content_hash": "9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08",
        "published_at": "2021-01-01"
    }
}
```

#### POST - **/legal/documents/{document}**

This endpoint stores the consent for the given document from the currently authenticated user.
The request is authorized only if the user model implements the `LegalConsenter` contract.

Along with the consent, the package records an **audit trail** (see below). You may optionally
pass a `locale` in the request body to override the detected application locale.

#### DELETE - **/legal/documents/{document}**

This endpoint withdraws every active consent the currently authenticated user has for the given
document. The consent rows are kept for the audit trail and simply flagged as withdrawn.

### Audit trail (GDPR)

Every acceptance stores the information needed to prove the consent later on:

- **content_hash**: a snapshot of the document `content_hash` at the moment of acceptance
- **accepted_at**: the acceptance timestamp
- **ip_address** and **user_agent**: captured from the request
- **locale**: the locale in effect at acceptance time

Because the IP address and the user agent are personal data (GDPR art. 32), they are **encrypted
at rest** by default using the application key. You can disable this via the `encrypt_audit_metadata`
config key (note that doing so stores them in clear text and makes them queryable).

### Versioning

Each document carries a `content_hash` derived from its `body`. When a user accepts a document,
that hash is copied onto the consent. `hasAcceptedLegalDocument()` returns `true` only when the
user has an **active** consent whose hash **matches the current document body**.

This means that editing a document's body automatically invalidates previously granted consents,
forcing users to accept the new version — no silent, stale consents.

### Accepting and withdrawing programmatically

``` php
// Accept the latest published document of a given type
$user->acceptDefaultLegalDocument('privacy-policy');

// Accept a specific document (optionally overriding the audit metadata)
$user->acceptLegalDocument($document, [
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'locale' => 'en',
]);

// Check the current state
$user->hasAcceptedLegalDocument($document);

// Withdraw every active consent for a document
$user->withdrawLegalDocument($document);
```

### Events

The package dispatches the following events, which you can listen to as usual:

- `Maize\LegalConsent\Events\LegalDocumentAccepted`
- `Maize\LegalConsent\Events\LegalConsentWithdrawn`

Each event exposes the `consenter`, the `document` and the `consent` involved.

### Auto accepting on registration

You can automatically accept all active legal documents when a user registers, which is useful
when the registration form contains the consent checkboxes.

Instead of manually wiring a listener (the `EventServiceProvider` is deprecated since Laravel 11),
simply enable the `auto_accept_on_registered` config key:

``` php
// config/legal-consent.php
'auto_accept_on_registered' => true,
```

The package will then listen to the framework's `Registered` event and accept every allowed
document type whose consent value (e.g. `privacy-policy_accepted`) is present in the request.

### Multi-language documents

The package intentionally does not ship a translation strategy: the document model is fully
swappable via the `legal_document_model` config key, so you can bring your own. A common recipe
is to extend the model and add [spatie/laravel-translatable](https://github.com/spatie/laravel-translatable):

``` php
<?php

namespace App\Models;

use Maize\LegalConsent\Models\LegalDocument as BaseLegalDocument;
use Spatie\Translatable\HasTranslations;

class LegalDocument extends BaseLegalDocument
{
    use HasTranslations;

    public array $translatable = ['body', 'notes'];
}
```

> **Note on `content_hash`**: with a translatable `body`, the automatically computed hash is
> derived from the full JSON payload of all locales. If you need per-locale integrity, override
> the hashing logic in your model (e.g. hash the accepted locale's text) to match your policy.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/maize-tech/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](https://github.com/maize-tech/.github/security/policy) on how to report security vulnerabilities.

## Credits

- [Enrico De Lazzari](https://github.com/enricodelazzari)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
