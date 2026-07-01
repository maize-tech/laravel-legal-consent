# Upgrading from 3.x to 4.0

Version 4.0 turns the package from a simple "consent checkbox log" into a GDPR-oriented consent
store: it adds an audit trail, consent withdrawal, document versioning and events. This requires
schema and API changes, so it is a **breaking release**. This guide walks you through the upgrade.

## 1. Update the dependency

```bash
composer require maize-tech/laravel-legal-consent:^4.0
```

## 2. Run the upgrade migration

Publish the migrations again and run them:

```bash
php artisan vendor:publish --tag="legal-consent-migrations"
php artisan migrate
```

This publishes `upgrade_legal_consent_tables_to_v4`, which:

- adds `version`, `status` and `content_hash` to `legal_documents` and backfills `content_hash`
  from the existing `body` of each document (existing documents are marked as `published`);
- rebuilds `legal_consents` with a surrogate `id` primary key and the new audit columns
  (`content_hash`, `ip_address`, `user_agent`, `locale`, `accepted_at`, `withdrawn_at`),
  copying every existing consent across and backfilling `accepted_at` from `created_at`.

The migration is guarded and idempotent: running it on a fresh 4.0 install is a safe no-op.

> **Do not re-run `create_legal_consent_tables`** on an existing install — only the upgrade
> migration should run against a 3.x database.

## 3. Implement the `LegalConsenter` contract

Models using the `HasLegalConsent` trait must now also implement the
`Maize\LegalConsent\Contracts\LegalConsenter` contract. The trait already provides every required
method, so this is a one-line change:

```diff
+use Maize\LegalConsent\Contracts\LegalConsenter;
 use Maize\LegalConsent\HasLegalConsent;

-class User extends Authenticatable
+class User extends Authenticatable implements LegalConsenter
 {
     use HasLegalConsent;
 }
```

## 4. Review the behavioural changes

- **`acceptLegalDocument()` now appends a consent row** instead of using `firstOrCreate`. Every
  acceptance (and every withdrawal) is a distinct, timestamped row — this is the audit trail.
  Its signature changed to `acceptLegalDocument(LegalDocument $document, array $meta = []): LegalConsent`.
- **`hasAcceptedLegalDocument()` now checks the current version.** It returns `true` only when the
  user has an *active* consent whose `content_hash` matches the document's current body. Editing a
  document's body therefore invalidates prior consents.
- **`acceptDefaultLegalDocument()` now returns `?LegalConsent`** (was `void`) and returns `null`
  when no published document exists for the type.
- **The default finder only serves `published` documents.** A document must have
  `status = published` *and* a `published_at` in the past to be returned.

## 5. Replace the manual registration listener

The manual wiring of `AcceptLegalDocumentListener` in `EventServiceProvider` (deprecated in
Laravel 11+) is no longer needed. Remove it and enable the config key instead:

```php
// config/legal-consent.php
'auto_accept_on_registered' => true,
```

## 6. Encryption of audit metadata

`ip_address` and `user_agent` are encrypted at rest by default (`encrypt_audit_metadata => true`),
using your application key.

- Make sure `APP_KEY` is set and stable across environments.
- If you rotate `APP_KEY`, follow Laravel's key rotation guidance (keep the previous key in
  `APP_PREVIOUS_KEYS`) so existing encrypted values remain readable.
- Set `encrypt_audit_metadata => false` if you need these columns in clear text (e.g. to query
  them). Existing rows are not re-encrypted/decrypted automatically when you flip this flag.
