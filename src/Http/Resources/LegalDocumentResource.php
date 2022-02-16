<?php

namespace Maize\LegalConsent\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LegalDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
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
