<?php

namespace Maize\LegalConsent\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Maize\LegalConsent\Models\LegalConsent;
use Maize\LegalConsent\Models\LegalDocument;

class LegalConsentWithdrawn
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Model $consenter,
        public LegalDocument $document,
        public LegalConsent $consent,
    ) {}
}
