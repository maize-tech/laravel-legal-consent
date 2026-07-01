<?php

namespace Maize\LegalConsent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Maize\LegalConsent\Exceptions\InvalidDocumentTypeException;
use Maize\LegalConsent\Models\LegalDocument;
use Maize\LegalConsent\Support\Config;

abstract class LegalDocumentFinder
{
    /**
     * @param  Builder<LegalDocument>  $builder
     * @return Builder<LegalDocument>
     */
    abstract public function query(Builder $builder, string $type): Builder;

    public function findForType(string $type, bool $fail = false): ?LegalDocument
    {
        $this->validateType($type);

        $model = $this->getLegalDocumentModel();
        $query = $this->query($model::query(), $type);

        /** @var LegalDocument|null $document */
        $document = Cache::remember(
            $model::legalCacheKey($type),
            Config::getDocumentCacheTtl(),
            fn () => $fail ? $query->firstOrFail() : $query->first()
        );

        return $document;
    }

    protected function getLegalDocumentModel(): LegalDocument
    {
        return Config::getLegalDocumentModel();
    }

    protected function validateType(string $type): void
    {
        if (! in_array($type, Config::getAllowedDocumentTypes(), true)) {
            throw new InvalidDocumentTypeException;
        }
    }
}
