<?php

namespace Maize\LegalConsent\Listeners;

class AcceptLegalDocumentListener
{
    public function handle(object $event): void
    {
        ($event->user ?? null)?->acceptDefaultLegalDocumentsFromRequest();
    }
}
