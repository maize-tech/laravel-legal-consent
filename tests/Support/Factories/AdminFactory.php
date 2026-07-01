<?php

namespace Maize\LegalConsent\Tests\Support\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maize\LegalConsent\Tests\Support\Models\Admin;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            //
        ];
    }
}
