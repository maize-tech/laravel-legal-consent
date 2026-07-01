<?php

use Illuminate\Support\Arr;
use Maize\LegalConsent\Models\LegalConsent;
use Maize\LegalConsent\Models\LegalDocument;
use Maize\LegalConsent\Tests\Support\Models\User;

it('computes a stable content hash from the body', function () {
    $document = LegalDocument::factory()->create([
        'body' => 'the very long legal text',
    ]);

    expect($document->content_hash)->toBe(hash('sha256', 'the very long legal text'));
});

it('auto increments the version per type', function () {
    $type = Arr::first(config('legal-consent.allowed_document_types'));

    $first = LegalDocument::factory()->create(['type' => $type]);
    $second = LegalDocument::factory()->create(['type' => $type]);

    expect($first->version)->toBe(1);
    expect($second->version)->toBe(2);
});

it('invalidates a consent when the document body changes', function () {
    $user = User::factory()->create();

    $document = LegalDocument::factory()->published()->create([
        'type' => Arr::first(config('legal-consent.allowed_document_types')),
        'body' => 'version one',
    ]);

    $user->acceptLegalDocument($document);

    expect($user->hasAcceptedLegalDocument($document))->toBeTrue();

    // Editing the body in place changes the content hash (and the cache key).
    $document->update(['body' => 'version two']);

    expect($user->hasAcceptedLegalDocument($document))->toBeFalse();

    // Re-accepting stores a new consent snapshot with the new hash.
    $user->acceptLegalDocument($document);

    expect($user->hasAcceptedLegalDocument($document))->toBeTrue();
    $this->assertDatabaseCount(LegalConsent::class, 2);
});
