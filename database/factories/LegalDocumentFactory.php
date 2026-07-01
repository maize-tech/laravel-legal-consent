<?php

namespace Maize\LegalConsent\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maize\LegalConsent\Enums\DocumentStatus;
use Maize\LegalConsent\Models\LegalDocument;

/**
 * @extends Factory<LegalDocument>
 */
class LegalDocumentFactory extends Factory
{
    protected $model = LegalDocument::class;

    public function definition(): array
    {
        return [
            'type' => 'tos',
            'body' => $this->faker->randomHtml(),
            'notes' => $this->faker->sentence(),
            'status' => DocumentStatus::Published,
            'published_at' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => DocumentStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => DocumentStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn () => [
            'status' => DocumentStatus::Archived,
        ]);
    }
}
