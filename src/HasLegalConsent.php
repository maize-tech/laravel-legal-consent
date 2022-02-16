<?php

namespace Maize\LegalConsent;

use Illuminate\Support\Facades\Cache;
use Maize\LegalConsent\Models\LegalDocument;

trait HasLegalConsent
{
    public function hasAcceptedDefaultLegalDocument(string $type): bool
    {
        $document = $this->findDefaultLegalDocumentForType($type);

        if (is_null($document)) {
            return true;
        }

        return $this->hasAcceptedLegalDocument($document);
    }

    public function hasAcceptedLegalDocument(LegalDocument $document): bool
    {
        $legalConsentModelClass = config('legal-consent.legal_consent_model');

        return Cache::remember(
            $this->legalCacheKey($document),
            config('legal-consent.cache.document_user_ttl'),
            fn () => $legalConsentModelClass::query()
                ->where([
                    'document_id' => $document->getKey(),
                    'user_id' => $this->getKey(),
                ])
                ->exists()
        );
    }

    protected function legalCacheKey(LegalDocument $document): string
    {
        return "legal.documents.{$document->getKey()}.users.{$this->getKey()}";
    }

    public function acceptDefaultLegalDocument(string $type): void
    {
        $document = $this->findDefaultLegalDocumentForType($type);

        $this->acceptLegalDocument($document);
    }

    public function acceptLegalDocument(LegalDocument $document): void
    {
        $legalConsentModelClass = config('legal-consent.legal_consent_model');

        $legalConsentModelClass::firstOrCreate([
            'document_id' => $document->getKey(),
            'user_id' => $this->getKey(),
        ]);

        Cache::forget(
            $this->legalCacheKey($document)
        );
    }

    protected function findDefaultLegalDocumentForType(string $type): ?LegalDocument
    {
        $finderClass = config('legal-consent.legal_document_finder');

        return app($finderClass)->findForType($type);
    }

    public function acceptDefaultLegalDocumentsFromRequest(): void
    {
        $types = config('legal-consent.allowed_document_types');

        foreach ($types as $type) {
            $this->acceptDefaultLegalDocumentFromRequest($type);
        }
    }

    public function acceptDefaultLegalDocumentFromRequest(string $type): void
    {
        if ($this->hasAcceptedFromRequest($type)) {
            $this->acceptDefaultLegalDocument($type);
        }
    }

    protected function hasAcceptedFromRequest(string $type): bool
    {
        $value = request()->get("{$type}_accepted");

        return in_array(
            $value,
            config('legal-consent.allowed_acceptable_values'),
            true
        );
    }
}
