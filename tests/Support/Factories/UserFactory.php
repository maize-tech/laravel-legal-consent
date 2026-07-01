<?php

namespace Maize\LegalConsent\Tests\Support\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maize\LegalConsent\Tests\Support\Models\User;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            //
        ];
    }
}
