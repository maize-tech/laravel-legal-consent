<?php

namespace Maize\LegalConsent;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class DefaultLegalDocumentFinder extends LegalDocumentFinder
{
    public function query(Builder $builder, string $type): Builder
    {
        return $builder
            ->where('type', $type)
            ->whereDate('published_at', '<=', Carbon::now())
            ->latest('published_at');
    }
}
