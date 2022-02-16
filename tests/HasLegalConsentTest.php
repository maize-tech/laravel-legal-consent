<?php

namespace Maize\LegalConsent\Tests;

use Carbon\Carbon;
use Maize\LegalConsent\Models\LegalDocument;
use Maize\LegalConsent\Tests\Models\User;

class HasLegalConsentTest extends TestCase
{
    /** @test */
    public function can_check_if_user_has_accepted_document()
    {
        $users = User::factory(2)->create();

        $doc1 = LegalDocument::factory()->create([
            'type' => config('legal-consent.allowed_document_types')[0],
            'published_at' => Carbon::now()->subDays(2),
        ]);

        $doc2 = LegalDocument::factory()->create([
            'type' => config('legal-consent.allowed_document_types')[1],
            'published_at' => Carbon::now()->subDays(2),
        ]);

        $this->assertFalse($users[0]->hasAcceptedLegalDocument($doc1));
        $this->assertFalse($users[0]->hasAcceptedLegalDocument($doc2));

        $this->assertFalse($users[1]->hasAcceptedLegalDocument($doc1));
        $this->assertFalse($users[1]->hasAcceptedLegalDocument($doc2));

        $users[0]->acceptLegalDocument($doc1);

        $this->assertTrue($users[0]->hasAcceptedLegalDocument($doc1));
        $this->assertFalse($users[0]->hasAcceptedLegalDocument($doc2));

        $this->assertFalse($users[1]->hasAcceptedLegalDocument($doc1));
        $this->assertFalse($users[1]->hasAcceptedLegalDocument($doc2));

        $users[1]->acceptLegalDocument($doc2);

        $this->assertTrue($users[0]->hasAcceptedLegalDocument($doc1));
        $this->assertFalse($users[0]->hasAcceptedLegalDocument($doc2));

        $this->assertFalse($users[1]->hasAcceptedLegalDocument($doc1));
        $this->assertTrue($users[1]->hasAcceptedLegalDocument($doc2));

        $users[1]->acceptLegalDocument($doc1);

        $this->assertTrue($users[0]->hasAcceptedLegalDocument($doc1));
        $this->assertFalse($users[0]->hasAcceptedLegalDocument($doc2));

        $this->assertTrue($users[1]->hasAcceptedLegalDocument($doc1));
        $this->assertTrue($users[1]->hasAcceptedLegalDocument($doc2));
    }

    /** @test */
    public function can_check_if_user_has_accepted_default_document()
    {
        $type1 = config('legal-consent.allowed_document_types')[0];
        $type2 = config('legal-consent.allowed_document_types')[1];

        $users = User::factory(2)->create();

        LegalDocument::factory()->create([
            'type' => $type1,
            'published_at' => Carbon::now()->subDays(2),
        ]);

        LegalDocument::factory()->create([
            'type' => $type2,
            'published_at' => Carbon::now()->subDays(2),
        ]);

        $this->assertFalse($users[0]->hasAcceptedDefaultLegalDocument($type1));
        $this->assertFalse($users[0]->hasAcceptedDefaultLegalDocument($type2));

        $this->assertFalse($users[1]->hasAcceptedDefaultLegalDocument($type1));
        $this->assertFalse($users[1]->hasAcceptedDefaultLegalDocument($type2));

        $users[0]->acceptDefaultLegalDocument($type1);

        $this->assertTrue($users[0]->hasAcceptedDefaultLegalDocument($type1));
        $this->assertFalse($users[0]->hasAcceptedDefaultLegalDocument($type2));

        $this->assertFalse($users[1]->hasAcceptedDefaultLegalDocument($type1));
        $this->assertFalse($users[1]->hasAcceptedDefaultLegalDocument($type2));

        $users[1]->acceptDefaultLegalDocument($type2);

        $this->assertTrue($users[0]->hasAcceptedDefaultLegalDocument($type1));
        $this->assertFalse($users[0]->hasAcceptedDefaultLegalDocument($type2));

        $this->assertFalse($users[1]->hasAcceptedDefaultLegalDocument($type1));
        $this->assertTrue($users[1]->hasAcceptedDefaultLegalDocument($type2));

        $users[1]->acceptDefaultLegalDocument($type1);

        $this->assertTrue($users[0]->hasAcceptedDefaultLegalDocument($type1));
        $this->assertFalse($users[0]->hasAcceptedDefaultLegalDocument($type2));

        $this->assertTrue($users[1]->hasAcceptedDefaultLegalDocument($type1));
        $this->assertTrue($users[1]->hasAcceptedDefaultLegalDocument($type2));
    }
}
