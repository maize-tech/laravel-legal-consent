<?php

namespace Maize\LegalConsent\Tests\Support\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Maize\LegalConsent\Contracts\LegalConsenter;
use Maize\LegalConsent\HasLegalConsent;
use Maize\LegalConsent\Tests\Support\Factories\AdminFactory;

class Admin extends Authenticatable implements LegalConsenter
{
    /** @use HasFactory<AdminFactory> */
    use HasFactory;

    use HasLegalConsent;

    /**
     * @return Factory<self>
     */
    protected static function newFactory(): Factory
    {
        return AdminFactory::new();
    }
}
