<?php

namespace Maize\LegalConsent\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Maize\LegalConsent\Database\Factories\LegalDocumentFactory;
use Maize\LegalConsent\Enums\DocumentStatus;

/**
 * @property int $id
 * @property string $type
 * @property int|null $version
 * @property DocumentStatus $status
 * @property string|null $body
 * @property string|null $notes
 * @property string|null $content_hash
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class LegalDocument extends Model
{
    /** @use HasFactory<LegalDocumentFactory> */
    use HasFactory;

    protected $table = 'legal_documents';

    protected $fillable = [
        'type',
        'version',
        'status',
        'body',
        'notes',
        'content_hash',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'status' => DocumentStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public static function legalCacheKey(string $type): string
    {
        return "legal.documents.{$type}";
    }

    /**
     * @return Factory<self>
     */
    protected static function newFactory(): Factory
    {
        return LegalDocumentFactory::new();
    }

    protected static function booted(): void
    {
        static::saving(function (self $document): void {
            $document->content_hash = hash('sha256', (string) $document->body);
        });

        static::creating(function (self $document): void {
            if (is_null($document->version)) {
                $document->version = ((int) static::query()
                    ->where('type', $document->type)
                    ->max('version')) + 1;
            }
        });

        $flushCache = fn (self $document) => Cache::forget(
            static::legalCacheKey($document->type)
        );

        static::created($flushCache);
        static::updated($flushCache);
        static::saved($flushCache);
        static::deleted($flushCache);
    }
}
