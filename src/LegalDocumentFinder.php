<?php

namespace Maize\LegalConsent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Maize\LegalConsent\Exceptions\InvalidDocumentTypeException;
use Maize\LegalConsent\Models\LegalDocument;

abstract class LegalDocumentFinder
{
    abstract public function query(Builder $builder, string $type): Builder;

    public function findForType(string $type, bool $fail = false): ?LegalDocument
    {
        $this->validateType($type);

        $model = $this->getLegalDocumentModel();
        $builder = $model::query();

        $first = $fail ? 'firstOrFail' : 'first';

        return Cache::remember(
            $model::legalCacheKey($type),
            config('legal-consent.cache.document_ttl'),
            fn () => $this
                ->query($builder, $type)
                ->$first()
        );
    }

    protected function getLegalDocumentModel(): LegalDocument
    {
        $legalDocumentModelClass = (string) config('legal-consent.legal_document_model');

        return new $legalDocumentModelClass;
    }

    protected function validateType(string $type): void
    {
        if (! in_array($type, config('legal-consent.allowed_document_types'))) {
            throw new InvalidDocumentTypeException;
        }
    }
}
