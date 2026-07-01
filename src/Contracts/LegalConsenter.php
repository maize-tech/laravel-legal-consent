<?php

namespace Maize\LegalConsent\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Maize\LegalConsent\Models\LegalConsent;
use Maize\LegalConsent\Models\LegalDocument;

interface LegalConsenter
{
    /**
     * @return MorphMany<LegalConsent, covariant \Illuminate\Database\Eloquent\Model>
     */
    public function legalConsents(): MorphMany;

    public function hasAcceptedLegalDocument(LegalDocument $document): bool;

    public function hasAcceptedDefaultLegalDocument(string $type): bool;

    /**
     * @param  array<string, mixed>  $meta
     */
    public function acceptLegalDocument(LegalDocument $document, array $meta = []): LegalConsent;

    public function acceptDefaultLegalDocument(string $type): ?LegalConsent;

    public function withdrawLegalDocument(LegalDocument $document): void;

    public function acceptDefaultLegalDocumentsFromRequest(): void;

    public function acceptDefaultLegalDocumentFromRequest(string $type): void;
}
