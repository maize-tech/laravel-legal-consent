<?php

namespace Maize\LegalConsent\Tests;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Maize\LegalConsent\Models\LegalDocument;

class LegalDocumentTest extends TestCase
{
    /** @test */
    public function can_get_a_document_by_type()
    {
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
    }

    /** @test */
    public function can_not_get_a_document_by_wrong_type_fail()
    {
        $route = $this->getRouteByPartialName(
            'documents.show',
            'wrong'
        );

        $this
            ->getJson($route)
            ->assertStatus(400);
    }

    /** @test */
    public function can_not_get_a_non_exist_document_fail()
    {
        $route = $this->getRouteByPartialName(
            'documents.show',
            Arr::first(config('legal-consent.allowed_document_types'))
        );

        $this
            ->getJson($route)
            ->assertStatus(404);
    }

    /** @test */
    public function can_not_get_a_non_published_document_fail()
    {
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
    }

    /** @test */
    public function can_get_latest_document()
    {
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
    }

    /** @test */
    public function can_get_document_per_type()
    {
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
            [ 'type' => $doc1->type ]
        );

        $route2 = $this->getRouteByPartialName(
            'documents.show',
            [ 'type' => $doc2->type ]
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
    }
}
