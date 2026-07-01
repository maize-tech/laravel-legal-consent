<?php

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Maize\LegalConsent\Models\LegalDocument;

it('can get a document by type', function () {
    $type = Arr::first(config('legal-consent.allowed_document_types'));

    $doc = LegalDocument::factory()->create([
        'type' => $type,
        'body' => 'body',
        'notes' => 'notes',
        'published_at' => Carbon::now()->subDays(2),
    ]);

    $route = $this->getRouteByPartialName(
        'documents.show',
        compact('type')
    );

    $this
        ->getJson($route)
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $doc->id,
                'type' => $doc->type,
                'body' => $doc->body,
                'notes' => $doc->notes,
                'published_at' => $doc->published_at->jsonSerialize(),
            ],
        ]);
});

it('can not get a document by wrong type', function () {
    $route = $this->getRouteByPartialName(
        'documents.show',
        'wrong'
    );

    $this
        ->getJson($route)
        ->assertStatus(400);
});

it('can not get a non exist document', function () {
    $route = $this->getRouteByPartialName(
        'documents.show',
        Arr::first(config('legal-consent.allowed_document_types'))
    );

    $this
        ->getJson($route)
        ->assertStatus(404);
});

it('can not get a non published document', function () {
    $type = Arr::first(config('legal-consent.allowed_document_types'));

    $doc = LegalDocument::factory()->create([
        'type' => $type,
        'body' => 'body',
        'notes' => 'notes',
        'published_at' => Carbon::now()->addDays(20),
    ]);

    $route = $this->getRouteByPartialName(
        'documents.show',
        compact('type')
    );

    $this
        ->getJson($route)
        ->assertStatus(404);
});

it('can not get a draft document even if published_at is in the past', function () {
    $type = Arr::first(config('legal-consent.allowed_document_types'));

    LegalDocument::factory()->draft()->create([
        'type' => $type,
        'published_at' => Carbon::now()->subDays(2),
    ]);

    $route = $this->getRouteByPartialName('documents.show', compact('type'));

    $this
        ->getJson($route)
        ->assertStatus(404);
});

it('can not get an archived document', function () {
    $type = Arr::first(config('legal-consent.allowed_document_types'));

    LegalDocument::factory()->archived()->create([
        'type' => $type,
        'published_at' => Carbon::now()->subDays(2),
    ]);

    $route = $this->getRouteByPartialName('documents.show', compact('type'));

    $this
        ->getJson($route)
        ->assertStatus(404);
});

it('can get latest document', function () {
    $type = Arr::first(config('legal-consent.allowed_document_types'));

    LegalDocument::factory()->create([
        'type' => $type,
        'published_at' => null,
    ]);

    LegalDocument::factory()->create([
        'type' => $type,
        'published_at' => Carbon::now()->addDays(2),
    ]);

    LegalDocument::factory()->create([
        'type' => $type,
        'published_at' => Carbon::now()->subDays(4),
    ]);

    $doc = LegalDocument::factory()->create([
        'type' => $type,
        'published_at' => Carbon::now()->subDays(2),
    ]);

    $route = $this->getRouteByPartialName(
        'documents.show',
        compact('type')
    );

    $this
        ->getJson($route)
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $doc->id,
            ],
        ]);
});

it('can get document per type', function () {
    $doc1 = LegalDocument::factory()->create([
        'type' => config('legal-consent.allowed_document_types')[0],
        'published_at' => Carbon::now()->subDays(2),
    ]);

    $doc2 = LegalDocument::factory()->create([
        'type' => config('legal-consent.allowed_document_types')[1],
        'published_at' => Carbon::now()->subDays(2),
    ]);

    $route1 = $this->getRouteByPartialName(
        'documents.show',
        ['type' => $doc1->type]
    );

    $route2 = $this->getRouteByPartialName(
        'documents.show',
        ['type' => $doc2->type]
    );

    $this
        ->getJson($route1)
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $doc1->id,
            ],
        ]);

    $this
        ->getJson($route2)
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $doc2->id,
            ],
        ]);
});
