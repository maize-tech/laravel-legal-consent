<?php

namespace Maize\LegalConsent\Tests;

use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Maize\LegalConsent\Listeners\AcceptLegalDocumentListener;
use Maize\LegalConsent\Models\LegalConsent;
use Maize\LegalConsent\Models\LegalDocument;
use Maize\LegalConsent\Tests\Models\User;

class AcceptLegalDocumentListenerTest extends TestCase
{
    /** @test */
    public function can_auto_accept_documents_after_registration()
    {
        $user = User::factory()->create();

        $type1 = config('legal-consent.allowed_document_types')[0];
        $type2 = config('legal-consent.allowed_document_types')[1];

        $doc1 = LegalDocument::factory()->create([
            'type' => $type1,
            'published_at' => Carbon::now()->subDays(2),
        ]);

        $doc2 = LegalDocument::factory()->create([
            'type' => $type2,
            'published_at' => Carbon::now()->subDays(2),
        ]);

        // mock request
        request()->merge([
            "{$type1}_accepted" => 1,
            "{$type2}_accepted" => 1,
        ]);

        $event = new Registered($user);

        $listener = new AcceptLegalDocumentListener();

        $listener->handle($event);

        $table = (new LegalConsent())->getTable();

        $this->assertDatabaseCount($table, 2);
    }

    /** @test */
    public function can_auto_accept_document_after_registration()
    {
        $user = User::factory()->create();

        $type1 = config('legal-consent.allowed_document_types')[0];
        $type2 = config('legal-consent.allowed_document_types')[1];

        $doc1 = LegalDocument::factory()->create([
            'type' => $type1,
            'published_at' => Carbon::now()->subDays(2),
        ]);

        $doc2 = LegalDocument::factory()->create([
            'type' => $type2,
            'published_at' => Carbon::now()->subDays(2),
        ]);

        // mock request
        request()->merge([
            "{$type1}_accepted" => 1,
        ]);

        $event = new Registered($user);

        $listener = new AcceptLegalDocumentListener();

        $listener->handle($event);

        $table = (new LegalConsent())->getTable();

        $this->assertDatabaseCount($table, 1);
    }

    /** @test */
    public function can_not_auto_accept_document_after_registration_if_request_is_empty()
    {
        $user = User::factory()->create();

        $type1 = config('legal-consent.allowed_document_types')[0];
        $type2 = config('legal-consent.allowed_document_types')[1];

        $doc1 = LegalDocument::factory()->create([
            'type' => $type1,
            'published_at' => Carbon::now()->subDays(2),
        ]);

        $doc2 = LegalDocument::factory()->create([
            'type' => $type2,
            'published_at' => Carbon::now()->subDays(2),
        ]);

        $event = new Registered($user);

        $listener = new AcceptLegalDocumentListener();

        $listener->handle($event);

        $table = (new LegalConsent())->getTable();

        $this->assertDatabaseCount($table, 0);
    }

    /** @test */
    public function ignore_event_if_not_have_user_setted()
    {
        $event = new class() {
        };

        $listener = new AcceptLegalDocumentListener();

        $listener->handle($event);

        $table = (new LegalConsent())->getTable();

        $this->assertDatabaseCount($table, 0);
    }
}
