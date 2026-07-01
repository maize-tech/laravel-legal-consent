<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('upgrades a legacy 1.x schema and backfills audit data', function () {
    // Recreate the original 1.x schema.
    Schema::dropIfExists('legal_consents');
    Schema::dropIfExists('legal_documents');

    Schema::create('legal_documents', function (Blueprint $table) {
        $table->id();
        $table->string('type');
        $table->mediumText('body');
        $table->string('notes');
        $table->dateTime('published_at')->nullable();
        $table->timestamps();
    });

    Schema::create('legal_consents', function (Blueprint $table) {
        $table->foreignId('document_id')->constrained('legal_documents')->cascadeOnDelete();
        $table->morphs('user');
        $table->timestamps();
    });

    $documentId = DB::table('legal_documents')->insertGetId([
        'type' => 'privacy-policy',
        'body' => 'legacy body',
        'notes' => 'legacy notes',
        'published_at' => '2021-01-01 00:00:00',
        'created_at' => '2021-01-01 00:00:00',
        'updated_at' => '2021-01-01 00:00:00',
    ]);

    DB::table('legal_consents')->insert([
        'document_id' => $documentId,
        'user_type' => 'user',
        'user_id' => 1,
        'created_at' => '2021-02-02 10:00:00',
        'updated_at' => '2021-02-02 10:00:00',
    ]);

    $migration = include __DIR__.'/../database/migrations/upgrade_legal_consent_tables_to_v2.php.stub';
    $migration->up();

    $document = DB::table('legal_documents')->first();
    expect($document->content_hash)->toBe(hash('sha256', 'legacy body'));
    expect($document->status)->toBe('published');

    $consent = DB::table('legal_consents')->first();
    expect($consent->content_hash)->toBe(hash('sha256', 'legacy body'));
    expect($consent->accepted_at)->toBe('2021-02-02 10:00:00');
});

it('is a safe no-op on a fresh 2.0 schema', function () {
    // The 2.0 schema is already created by the base TestCase, so running the
    // upgrade migration must not throw and must leave every column in place.
    $migration = include __DIR__.'/../database/migrations/upgrade_legal_consent_tables_to_v2.php.stub';

    $migration->up();

    expect(Schema::hasColumns('legal_documents', ['version', 'status', 'content_hash']))->toBeTrue();
    expect(Schema::hasColumns('legal_consents', [
        'id',
        'content_hash',
        'ip_address',
        'user_agent',
        'locale',
        'accepted_at',
        'withdrawn_at',
    ]))->toBeTrue();
});
