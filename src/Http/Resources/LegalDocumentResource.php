<?php

namespace Maize\LegalConsent\Http\Resources;

use Illuminate\Http\Request;
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
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'version' => $this->version,
            'status' => $this->status->value,
            'body' => $this->body,
            'notes' => $this->notes,
            'content_hash' => $this->content_hash,
            'published_at' => $this->published_at,
        ];
    }
}
