<?php

namespace Maize\LegalConsent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;
use Maize\LegalConsent\Support\Config;

/**
 * @property int $id
 * @property int $document_id
 * @property string $user_type
 * @property int $user_id
 * @property string|null $content_hash
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $locale
 * @property Carbon|null $accepted_at
 * @property Carbon|null $withdrawn_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class LegalConsent extends Pivot
{
    /** @use HasFactory<Factory<LegalConsent>> */
    use HasFactory;

    public $incrementing = true;

    protected $table = 'legal_consents';

    protected $fillable = [
        'document_id',
        'user_type',
        'user_id',
        'content_hash',
        'ip_address',
        'user_agent',
        'locale',
        'accepted_at',
        'withdrawn_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        $casts = [
            'accepted_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];

        if (Config::shouldEncryptAuditMetadata()) {
            $casts['ip_address'] = 'encrypted';
            $casts['user_agent'] = 'encrypted';
        }

        return $casts;
    }

    /**
     * @param  Builder<LegalConsent>  $query
     * @return Builder<LegalConsent>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('withdrawn_at');
    }

    /**
     * @param  Builder<LegalConsent>  $query
     * @return Builder<LegalConsent>
     */
    public function scopeWithdrawn(Builder $query): Builder
    {
        return $query->whereNotNull('withdrawn_at');
    }
}
