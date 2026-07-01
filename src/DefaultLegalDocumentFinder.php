<?php

namespace Maize\LegalConsent;

use Illuminate\Database\Eloquent\Builder;
use Maize\LegalConsent\Enums\DocumentStatus;

class DefaultLegalDocumentFinder extends LegalDocumentFinder
{
    public function query(Builder $builder, string $type): Builder
    {
        return $builder
            ->where('type', $type)
            ->where('status', DocumentStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at');
    }
}
