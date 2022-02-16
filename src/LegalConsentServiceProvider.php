<?php

namespace Maize\LegalConsent;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LegalConsentServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-legal-consent')
            ->hasConfigFile()
            ->hasRoute('routes')
            ->hasMigration('create_legal_consent_tables');
    }
}
