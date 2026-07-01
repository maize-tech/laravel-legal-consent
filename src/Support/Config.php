<?php

namespace Maize\LegalConsent\Support;

use Illuminate\Support\Arr;
use Maize\LegalConsent\DefaultLegalDocumentFinder;
use Maize\LegalConsent\LegalDocumentFinder;
use Maize\LegalConsent\Models\LegalConsent;
use Maize\LegalConsent\Models\LegalDocument;

class Config
{
    public static function getLegalDocumentModel(): LegalDocument
    {
        /** @var class-string<LegalDocument> $model */
        $model = config('legal-consent.legal_document_model')
            ?? LegalDocument::class;

        return new $model;
    }

    /**
     * @return class-string<LegalConsent>
     */
    public static function getLegalConsentModelClass(): string
    {
        /** @var class-string<LegalConsent> $model */
        $model = config('legal-consent.legal_consent_model')
            ?? LegalConsent::class;

        return $model;
    }

    public static function getFinder(): LegalDocumentFinder
    {
        $finder = config('legal-consent.legal_document_finder')
            ?? DefaultLegalDocumentFinder::class;

        return app($finder);
    }

    /**
     * @return array<int, string>
     */
    public static function getAllowedDocumentTypes(): array
    {
        return Arr::wrap(config('legal-consent.allowed_document_types') ?? []);
    }

    /**
     * @return array<int, mixed>
     */
    public static function getAllowedAcceptableValues(): array
    {
        return Arr::wrap(config('legal-consent.allowed_acceptable_values') ?? []);
    }

    public static function getDocumentCacheTtl(): int
    {
        return (int) (config('legal-consent.cache.document_ttl') ?? 3600);
    }

    public static function getDocumentUserCacheTtl(): int
    {
        return (int) (config('legal-consent.cache.document_user_ttl') ?? 3600);
    }

    public static function shouldEncryptAuditMetadata(): bool
    {
        return (bool) (config('legal-consent.encrypt_audit_metadata') ?? true);
    }

    public static function shouldAutoAcceptOnRegistered(): bool
    {
        return (bool) (config('legal-consent.auto_accept_on_registered') ?? false);
    }

    public static function routesEnabled(): bool
    {
        return (bool) (config('legal-consent.routes.enabled') ?? true);
    }

    public static function getRoutePrefix(): string
    {
        return (string) (config('legal-consent.routes.prefix') ?? 'legal');
    }

    public static function getRouteName(): string
    {
        return (string) (config('legal-consent.routes.name') ?? 'legal');
    }

    /**
     * @return array<int, string>
     */
    public static function getRouteMiddleware(): array
    {
        return Arr::wrap(config('legal-consent.routes.middleware') ?? []);
    }

    /**
     * @return array<int, string>
     */
    public static function getShowMiddleware(): array
    {
        return Arr::wrap(config('legal-consent.routes.endpoints.show.middleware') ?? []);
    }

    /**
     * @return array<int, string>
     */
    public static function getConsentMiddleware(): array
    {
        return Arr::wrap(config('legal-consent.routes.endpoints.consent.middleware') ?? []);
    }

    /**
     * @return array<int, string>
     */
    public static function getWithdrawMiddleware(): array
    {
        return Arr::wrap(config('legal-consent.routes.endpoints.withdraw.middleware') ?? []);
    }
}
