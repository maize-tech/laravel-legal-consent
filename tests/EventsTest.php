<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Maize\LegalConsent\Events\LegalConsentWithdrawn;
use Maize\LegalConsent\Events\LegalDocumentAccepted;
use Maize\LegalConsent\Models\LegalDocument;
use Maize\LegalConsent\Tests\Support\Models\User;

it('dispatches an event when a document is accepted', function () {
    Event::fake([LegalDocumentAccepted::class]);

    $user = User::factory()->create();

    $document = LegalDocument::factory()->published()->create([
        'type' => Arr::first(config('legal-consent.allowed_document_types')),
    ]);

    $user->acceptLegalDocument($document);

    Event::assertDispatched(LegalDocumentAccepted::class, fn ($event) => $event->document->is($document)
        && $event->consenter->is($user)
    );
});

it('dispatches an event when a consent is withdrawn', function () {
    $user = User::factory()->create();

    $document = LegalDocument::factory()->published()->create([
        'type' => Arr::first(config('legal-consent.allowed_document_types')),
    ]);

    $user->acceptLegalDocument($document);

    Event::fake([LegalConsentWithdrawn::class]);

    $user->withdrawLegalDocument($document);

    Event::assertDispatched(LegalConsentWithdrawn::class, fn ($event) => $event->document->is($document)
        && $event->consenter->is($user)
    );
});
