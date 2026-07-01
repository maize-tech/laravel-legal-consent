<?php

namespace Maize\LegalConsent;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Maize\LegalConsent\Listeners\AcceptLegalDocumentListener;
use Maize\LegalConsent\Support\Config;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
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
            ->hasMigration('create_legal_consent_tables')
            ->hasMigration('upgrade_legal_consent_tables_to_v4')
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('maize-tech/laravel-legal-consent');
            });
    }

    public function packageBooted(): void
    {
        Event::listen(Registered::class, function (Registered $event): void {
            if (Config::shouldAutoAcceptOnRegistered()) {
                app(AcceptLegalDocumentListener::class)->handle($event);
            }
        });
    }
}
