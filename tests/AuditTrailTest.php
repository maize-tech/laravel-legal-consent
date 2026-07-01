<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Maize\LegalConsent\Models\LegalConsent;
use Maize\LegalConsent\Models\LegalDocument;
use Maize\LegalConsent\Tests\Support\Models\User;

it('captures the audit metadata on acceptance', function () {
    $user = User::factory()->create();

    $document = LegalDocument::factory()->published()->create([
        'type' => Arr::first(config('legal-consent.allowed_document_types')),
    ]);

    $consent = $user->acceptLegalDocument($document, [
        'ip_address' => '1.2.3.4',
        'user_agent' => 'PestBrowser/1.0',
        'locale' => 'it',
    ]);

    expect($consent->content_hash)->toBe($document->content_hash);
    expect($consent->accepted_at)->not->toBeNull();
    expect($consent->ip_address)->toBe('1.2.3.4');
    expect($consent->user_agent)->toBe('PestBrowser/1.0');
    expect($consent->locale)->toBe('it');
});

it('encrypts ip address and user agent at rest', function () {
    config()->set('legal-consent.encrypt_audit_metadata', true);

    $user = User::factory()->create();

    $document = LegalDocument::factory()->published()->create([
        'type' => Arr::first(config('legal-consent.allowed_document_types')),
    ]);

    $user->acceptLegalDocument($document, [
        'ip_address' => '1.2.3.4',
        'user_agent' => 'PestBrowser/1.0',
    ]);

    // Raw DB values must not be readable as plain text...
    $raw = DB::table('legal_consents')->first();
    expect($raw->ip_address)->not->toBe('1.2.3.4');
    expect($raw->user_agent)->not->toBe('PestBrowser/1.0');

    // ...but the model transparently decrypts them.
    $consent = LegalConsent::first();
    expect($consent->ip_address)->toBe('1.2.3.4');
    expect($consent->user_agent)->toBe('PestBrowser/1.0');
});

it('stores audit metadata in clear text when encryption is disabled', function () {
    config()->set('legal-consent.encrypt_audit_metadata', false);

    $user = User::factory()->create();

    $document = LegalDocument::factory()->published()->create([
        'type' => Arr::first(config('legal-consent.allowed_document_types')),
    ]);

    $user->acceptLegalDocument($document, ['ip_address' => '1.2.3.4']);

    $raw = DB::table('legal_consents')->first();
    expect($raw->ip_address)->toBe('1.2.3.4');
});
