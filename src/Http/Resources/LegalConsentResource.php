<?php

namespace Maize\LegalConsent\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Maize\LegalConsent\Models\LegalConsent;

/**
 * @mixin LegalConsent
 */
class LegalConsentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Note: ip_address and user_agent are intentionally omitted for data
     * minimization (GDPR) — they are audit-only fields, not meant to be exposed.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'document_id' => $this->document_id,
            'content_hash' => $this->content_hash,
            'locale' => $this->locale,
            'accepted_at' => $this->accepted_at,
            'withdrawn_at' => $this->withdrawn_at,
        ];
    }
}
