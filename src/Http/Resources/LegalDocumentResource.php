<?php

namespace Maize\LegalConsent\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Maize\LegalConsent\Models\LegalDocument;

/**
 * @mixin LegalDocument
 */
class LegalDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'body' => $this->body,
            'notes' => $this->notes,
            'published_at' => $this->published_at,
        ];
    }
}
