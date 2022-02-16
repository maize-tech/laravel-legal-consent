<?php

namespace Maize\LegalConsent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LegalDocument extends Model
{
    use HasFactory;

    protected $table = 'legal_documents';

    protected $fillable = [
        'type',
        'body',
        'notes',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public static function legalCacheKey(string $type): string
    {
        return "legal.documents.{$type}";
    }

    protected static function booted()
    {
        $flushCache = fn (self $document) => Cache::forget(
            static::legalCacheKey($document->type)
        );

        static::created($flushCache);
        static::updated($flushCache);
        static::saved($flushCache);
        static::deleted($flushCache);
    }
}
