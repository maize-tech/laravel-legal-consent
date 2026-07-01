<?php

use Illuminate\Support\Arr;
use Maize\LegalConsent\Models\LegalConsent;
use Maize\LegalConsent\Models\LegalDocument;
use Maize\LegalConsent\Tests\Support\Models\User;

it('can withdraw a previously accepted document', function () {
    $user = User::factory()->create();

    $document = LegalDocument::factory()->published()->create([
        'type' => Arr::first(config('legal-consent.allowed_document_types')),
    ]);

    $user->acceptLegalDocument($document);

    expect($user->hasAcceptedLegalDocument($document))->toBeTrue();

    $user->withdrawLegalDocument($document);

    expect($user->hasAcceptedLegalDocument($document))->toBeFalse();

    // The consent row is kept for the audit trail, only flagged as withdrawn.
    $this->assertDatabaseCount(LegalConsent::class, 1);
    expect(LegalConsent::first()->withdrawn_at)->not->toBeNull();
});

it('can re accept a document after withdrawal', function () {
    $user = User::factory()->create();

    $document = LegalDocument::factory()->published()->create([
        'type' => Arr::first(config('legal-consent.allowed_document_types')),
    ]);

    $user->acceptLegalDocument($document);
    $user->withdrawLegalDocument($document);
    $user->acceptLegalDocument($document);

    expect($user->hasAcceptedLegalDocument($document))->toBeTrue();

    // One withdrawn row + one active row.
    $this->assertDatabaseCount(LegalConsent::class, 2);
    expect(LegalConsent::active()->count())->toBe(1);
    expect(LegalConsent::withdrawn()->count())->toBe(1);
});

it('can withdraw a consent through the api', function () {
    $user = User::factory()->create();

    $document = LegalDocument::factory()->published()->create([
        'type' => Arr::first(config('legal-consent.allowed_document_types')),
    ]);

    $user->acceptLegalDocument($document);

    $route = $this->getRouteByPartialName('documents.withdraw', $document->getKey());

    $this
        ->actingAs($user, 'api')
        ->deleteJson($route)
        ->assertStatus(204);

    expect($user->fresh()->hasAcceptedLegalDocument($document))->toBeFalse();
});

it('can not withdraw a consent if user is not authenticated', function () {
    $document = LegalDocument::factory()->published()->create([
        'type' => Arr::first(config('legal-consent.allowed_document_types')),
    ]);

    $route = $this->getRouteByPartialName('documents.withdraw', $document->getKey());

    $this
        ->deleteJson($route)
        ->assertStatus(401);
});
