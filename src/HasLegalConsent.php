<?php

namespace Maize\LegalConsent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Cache;
use Maize\LegalConsent\Events\LegalConsentWithdrawn;
use Maize\LegalConsent\Events\LegalDocumentAccepted;
use Maize\LegalConsent\Models\LegalConsent;
use Maize\LegalConsent\Models\LegalDocument;
use Maize\LegalConsent\Support\Config;

/**
 * @mixin Model
 */
trait HasLegalConsent
{
    public static function bootHasLegalConsent(): void
    {
        static::deleted(
            fn ($model) => $model->legalConsents()->delete()
        );
    }

    /**
     * @return MorphMany<LegalConsent, covariant $this>
     */
    public function legalConsents(): MorphMany
    {
        return $this->morphMany(Config::getLegalConsentModelClass(), 'user');
    }

    public function hasAcceptedDefaultLegalDocument(string $type): bool
    {
        $document = $this->findDefaultLegalDocumentForType($type);

        if (is_null($document)) {
            return true;
        }

        return $this->hasAcceptedLegalDocument($document);
    }

    public function hasAcceptedLegalDocument(LegalDocument $document): bool
    {
        return Cache::remember(
            $this->legalCacheKey($document),
            Config::getDocumentUserCacheTtl(),
            fn () => $this
                ->legalConsents()
                ->where('document_id', $document->getKey())
                ->where('content_hash', $document->content_hash)
                ->whereNull('withdrawn_at')
                ->exists()
        );
    }

    protected function legalCacheKey(LegalDocument $document): string
    {
        return "legal.documents.{$document->getKey()}.{$document->content_hash}.{$this->getMorphClass()}.{$this->getKey()}";
    }

    public function acceptDefaultLegalDocument(string $type): ?LegalConsent
    {
        $document = $this->findDefaultLegalDocumentForType($type);

        if (is_null($document)) {
            return null;
        }

        return $this->acceptLegalDocument($document);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function acceptLegalDocument(LegalDocument $document, array $meta = []): LegalConsent
    {
        $request = request();

        /** @var LegalConsent $consent */
        $consent = $this->legalConsents()->create([
            'document_id' => $document->getKey(),
            'content_hash' => $document->content_hash,
            'ip_address' => $meta['ip_address'] ?? $request->ip(),
            'user_agent' => $meta['user_agent'] ?? $request->userAgent(),
            'locale' => $meta['locale'] ?? app()->getLocale(),
            'accepted_at' => now(),
        ]);

        Cache::forget(
            $this->legalCacheKey($document)
        );

        event(new LegalDocumentAccepted($this, $document, $consent));

        return $consent;
    }

    public function withdrawLegalDocument(LegalDocument $document): void
    {
        $this
            ->legalConsents()
            ->where('document_id', $document->getKey())
            ->whereNull('withdrawn_at')
            ->get()
            ->each(function (LegalConsent $consent) use ($document): void {
                $consent->update(['withdrawn_at' => now()]);

                event(new LegalConsentWithdrawn($this, $document, $consent));
            });

        Cache::forget(
            $this->legalCacheKey($document)
        );
    }

    protected function findDefaultLegalDocumentForType(string $type): ?LegalDocument
    {
        return Config::getFinder()->findForType($type);
    }

    public function acceptDefaultLegalDocumentsFromRequest(): void
    {
        foreach (Config::getAllowedDocumentTypes() as $type) {
            $this->acceptDefaultLegalDocumentFromRequest($type);
        }
    }

    public function acceptDefaultLegalDocumentFromRequest(string $type): void
    {
        if ($this->hasAcceptedFromRequest($type)) {
            $this->acceptDefaultLegalDocument($type);
        }
    }

    protected function hasAcceptedFromRequest(string $type): bool
    {
        $value = request()->input("{$type}_accepted");

        return in_array(
            $value,
            Config::getAllowedAcceptableValues(),
            true
        );
    }
}
