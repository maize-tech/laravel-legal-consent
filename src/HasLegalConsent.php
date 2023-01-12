<?php

namespace Maize\LegalConsent;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Cache;
use Maize\LegalConsent\Models\LegalDocument;

trait HasLegalConsent
{
    public static function bootHasLegalConsent(): void
    {
        static::deleted(
            fn ($model) => $model->legalConsents()->delete()
        );
    }

    public function legalConsents(): MorphMany
    {
        return $this->morphMany(
            config('legal-consent.legal_consent_model'),
            'user'
        );
    }

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
        return Cache::remember(
            $this->legalCacheKey($document),
            config('legal-consent.cache.document_user_ttl'),
            fn () => $this
                ->legalConsents()
                ->where('document_id', $document->getKey())
                ->exists()
        );
    }

    protected function legalCacheKey(LegalDocument $document): string
    {
        return "legal.documents.{$document->getKey()}.{$this->getMorphClass()}.{$this->getKey()}";
    }

    public function acceptDefaultLegalDocument(string $type): void
    {
        $document = $this->findDefaultLegalDocumentForType($type);

        $this->acceptLegalDocument($document);
    }

    public function acceptLegalDocument(LegalDocument $document): void
    {
        $this->legalConsents()->firstOrCreate([
            'document_id' => $document->getKey(),
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
