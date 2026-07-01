<?php

use Carbon\Carbon;
use Maize\LegalConsent\Models\LegalConsent;
use Maize\LegalConsent\Models\LegalDocument;
use Maize\LegalConsent\Tests\Support\Models\Admin;
use Maize\LegalConsent\Tests\Support\Models\User;

it('can check if user has accepted document', function () {
    $users = User::factory(2)->create();

    $doc1 = LegalDocument::factory()->create([
        'type' => config('legal-consent.allowed_document_types')[0],
        'published_at' => Carbon::now()->subDays(2),
    ]);

    $doc2 = LegalDocument::factory()->create([
        'type' => config('legal-consent.allowed_document_types')[1],
        'published_at' => Carbon::now()->subDays(2),
    ]);

    expect($users[0]->hasAcceptedLegalDocument($doc1))->toBeFalse();
    expect($users[0]->hasAcceptedLegalDocument($doc2))->toBeFalse();

    expect($users[1]->hasAcceptedLegalDocument($doc1))->toBeFalse();
    expect($users[1]->hasAcceptedLegalDocument($doc2))->toBeFalse();

    $users[0]->acceptLegalDocument($doc1);

    expect($users[0]->hasAcceptedLegalDocument($doc1))->toBeTrue();
    expect($users[0]->hasAcceptedLegalDocument($doc2))->toBeFalse();

    expect($users[1]->hasAcceptedLegalDocument($doc1))->toBeFalse();
    expect($users[1]->hasAcceptedLegalDocument($doc2))->toBeFalse();

    $users[1]->acceptLegalDocument($doc2);

    expect($users[0]->hasAcceptedLegalDocument($doc1))->toBeTrue();
    expect($users[0]->hasAcceptedLegalDocument($doc2))->toBeFalse();

    expect($users[1]->hasAcceptedLegalDocument($doc1))->toBeFalse();
    expect($users[1]->hasAcceptedLegalDocument($doc2))->toBeTrue();

    $users[1]->acceptLegalDocument($doc1);

    expect($users[0]->hasAcceptedLegalDocument($doc1))->toBeTrue();
    expect($users[0]->hasAcceptedLegalDocument($doc2))->toBeFalse();

    expect($users[1]->hasAcceptedLegalDocument($doc1))->toBeTrue();
    expect($users[1]->hasAcceptedLegalDocument($doc2))->toBeTrue();
});

it('can check if user has accepted default document', function () {
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

    expect($users[0]->hasAcceptedDefaultLegalDocument($type1))->toBeFalse();
    expect($users[0]->hasAcceptedDefaultLegalDocument($type2))->toBeFalse();

    expect($users[1]->hasAcceptedDefaultLegalDocument($type1))->toBeFalse();
    expect($users[1]->hasAcceptedDefaultLegalDocument($type2))->toBeFalse();

    $users[0]->acceptDefaultLegalDocument($type1);

    expect($users[0]->hasAcceptedDefaultLegalDocument($type1))->toBeTrue();
    expect($users[0]->hasAcceptedDefaultLegalDocument($type2))->toBeFalse();

    expect($users[1]->hasAcceptedDefaultLegalDocument($type1))->toBeFalse();
    expect($users[1]->hasAcceptedDefaultLegalDocument($type2))->toBeFalse();

    $users[1]->acceptDefaultLegalDocument($type2);

    expect($users[0]->hasAcceptedDefaultLegalDocument($type1))->toBeTrue();
    expect($users[0]->hasAcceptedDefaultLegalDocument($type2))->toBeFalse();

    expect($users[1]->hasAcceptedDefaultLegalDocument($type1))->toBeFalse();
    expect($users[1]->hasAcceptedDefaultLegalDocument($type2))->toBeTrue();

    $users[1]->acceptDefaultLegalDocument($type1);

    expect($users[0]->hasAcceptedDefaultLegalDocument($type1))->toBeTrue();
    expect($users[0]->hasAcceptedDefaultLegalDocument($type2))->toBeFalse();

    expect($users[1]->hasAcceptedDefaultLegalDocument($type1))->toBeTrue();
    expect($users[1]->hasAcceptedDefaultLegalDocument($type2))->toBeTrue();
});

it('can delete associated legal consents on user delete', function () {
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
});

it('can allow multiple user types', function () {
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
});
