<?php

use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Maize\LegalConsent\Listeners\AcceptLegalDocumentListener;
use Maize\LegalConsent\Models\LegalConsent;
use Maize\LegalConsent\Models\LegalDocument;
use Maize\LegalConsent\Tests\Models\User;

it('can auto accept documents after registration', function () {
    $user = User::factory()->create();

    $type1 = config('legal-consent.allowed_document_types')[0];
    $type2 = config('legal-consent.allowed_document_types')[1];

    LegalDocument::factory()->create([
        'type' => $type1,
        'published_at' => Carbon::now()->subDays(2),
    ]);

    LegalDocument::factory()->create([
        'type' => $type2,
        'published_at' => Carbon::now()->subDays(2),
    ]);

    // mock request
    request()->merge([
        "{$type1}_accepted" => 1,
        "{$type2}_accepted" => 1,
    ]);

    $event = new Registered($user);

    $listener = new AcceptLegalDocumentListener;

    $listener->handle($event);

    $table = (new LegalConsent)->getTable();

    $this->assertDatabaseCount($table, 2);
});

it('can auto accept document after registration', function () {
    $user = User::factory()->create();

    $type1 = config('legal-consent.allowed_document_types')[0];
    $type2 = config('legal-consent.allowed_document_types')[1];

    LegalDocument::factory()->create([
        'type' => $type1,
        'published_at' => Carbon::now()->subDays(2),
    ]);

    LegalDocument::factory()->create([
        'type' => $type2,
        'published_at' => Carbon::now()->subDays(2),
    ]);

    // mock request
    request()->merge([
        "{$type1}_accepted" => 1,
    ]);

    $event = new Registered($user);

    $listener = new AcceptLegalDocumentListener;

    $listener->handle($event);

    $table = (new LegalConsent)->getTable();

    $this->assertDatabaseCount($table, 1);
});

it('can not auto accept document after registration if request is empty', function () {
    $user = User::factory()->create();

    $type1 = config('legal-consent.allowed_document_types')[0];
    $type2 = config('legal-consent.allowed_document_types')[1];

    LegalDocument::factory()->create([
        'type' => $type1,
        'published_at' => Carbon::now()->subDays(2),
    ]);

    LegalDocument::factory()->create([
        'type' => $type2,
        'published_at' => Carbon::now()->subDays(2),
    ]);

    $event = new Registered($user);

    $listener = new AcceptLegalDocumentListener;

    $listener->handle($event);

    $table = (new LegalConsent)->getTable();

    $this->assertDatabaseCount($table, 0);
});

it('ignores event if no user is set', function () {
    $event = new class {};

    $listener = new AcceptLegalDocumentListener;

    $listener->handle($event);

    $table = (new LegalConsent)->getTable();

    $this->assertDatabaseCount($table, 0);
});
