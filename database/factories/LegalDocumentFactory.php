<?php

namespace Maize\LegalConsent\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maize\LegalConsent\Models\LegalDocument;

class LegalDocumentFactory extends Factory
{
    protected $model = LegalDocument::class;

    public function definition()
    {
        return [
            'type' => 'tos',
            'body' => $this->faker->randomHtml(),
            'notes' => $this->faker->randomHtml(),
            'published_at' => null,
        ];
    }
}
