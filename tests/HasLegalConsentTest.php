<?php

namespace Maize\LegalConsent\Tests;

use Carbon\Carbon;
use Maize\LegalConsent\Models\LegalConsent;
use Maize\LegalConsent\Models\LegalDocument;
use Maize\LegalConsent\Tests\Models\Admin;
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

    /** @test */
    public function can_delete_associated_legal_consents_on_user_delete()
    {
        $type1 = config('legal-consent.allowed_document_types')[0];
        $type2 = config('legal-consent.allowed_document_types')[1];

        $user = User::factory()->create();

        LegalDocument::factory()->create([
            'type' => $type1,
            'published_at' => Carbon::now()->subDays(2),
        ]);

        LegalDocument::factory()->create([
            'type' => $type2,
            'published_at' => Carbon::now()->subDays(2),
        ]);

        $user->acceptDefaultLegalDocument($type1);
        $user->acceptDefaultLegalDocument($type2);

        $this->assertDatabaseCount(LegalConsent::class, 2);

        $user->delete();

        $this->assertDatabaseCount(LegalConsent::class, 0);
    }

    /** @test */
    public function can_allow_multiple_user_types()
    {
        $type = config('legal-consent.allowed_document_types')[0];

        $user = User::factory()->create();
        $admin = Admin::factory()->create();

        $document = LegalDocument::factory()->create([
            'type' => $type,
            'published_at' => Carbon::now()->subDays(2),
        ]);

        $user->acceptDefaultLegalDocument($type);
        $admin->acceptDefaultLegalDocument($type);

        $this->assertDatabaseHas(LegalConsent::class, [
            'user_type' => $user->getMorphClass(),
            'user_id' => $user->getKey(),
            'document_id' => $document->getKey(),
        ]);

        $this->assertDatabaseHas(LegalConsent::class, [
            'user_type' => $admin->getMorphClass(),
            'user_id' => $admin->getKey(),
            'document_id' => $document->getKey(),
        ]);
    }
}
