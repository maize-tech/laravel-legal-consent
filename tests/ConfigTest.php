<?php

use Maize\LegalConsent\DefaultLegalDocumentFinder;
use Maize\LegalConsent\Models\LegalConsent;
use Maize\LegalConsent\Models\LegalDocument;
use Maize\LegalConsent\Support\Config;

it('resolves the configured models and finder', function () {
    expect(Config::getLegalDocumentModel())->toBeInstanceOf(LegalDocument::class);
    expect(Config::getLegalConsentModelClass())->toBe(LegalConsent::class);
    expect(Config::getFinder())->toBeInstanceOf(DefaultLegalDocumentFinder::class);
});

it('falls back to sensible defaults when config is missing', function () {
    config()->set('legal-consent.cache.document_ttl', null);
    config()->set('legal-consent.encrypt_audit_metadata', null);
    config()->set('legal-consent.auto_accept_on_registered', null);
    config()->set('legal-consent.routes.prefix', null);

    expect(Config::getDocumentCacheTtl())->toBe(3600);
    expect(Config::shouldEncryptAuditMetadata())->toBeTrue();
    expect(Config::shouldAutoAcceptOnRegistered())->toBeFalse();
    expect(Config::getRoutePrefix())->toBe('legal');
});

it('reads overridden config values', function () {
    config()->set('legal-consent.encrypt_audit_metadata', false);
    config()->set('legal-consent.cache.document_user_ttl', 60);

    expect(Config::shouldEncryptAuditMetadata())->toBeFalse();
    expect(Config::getDocumentUserCacheTtl())->toBe(60);
});

it('always wraps middleware into arrays', function () {
    config()->set('legal-consent.routes.endpoints.consent.middleware', 'auth:api');

    expect(Config::getConsentMiddleware())->toBe(['auth:api']);
    expect(Config::getWithdrawMiddleware())->toBeArray();
    expect(Config::getShowMiddleware())->toBeArray();
});
