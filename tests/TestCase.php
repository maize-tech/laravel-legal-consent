<?php

namespace Maize\LegalConsent\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maize\LegalConsent\LegalConsentServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Maize\\LegalConsent\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LegalConsentServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        config()->set('auth.guards', [
            'web' => [
                'driver' => 'session',
                'provider' => 'users',
            ],
            'api' => [
                'driver' => 'session',
                'provider' => 'users',
                'hash' => false,
            ],
        ]);

        config()->set('legal-consent.allowed_document_types', [
            'type1',
            'type2',
        ]);

        include_once __DIR__.'/../database/migrations/create_users_table.php.stub';
        (new \CreateUsersTable())->up();

        include_once __DIR__.'/../database/migrations/create_admins_table.php.stub';
        (new \CreateAdminsTable())->up();

        include_once __DIR__.'/../database/migrations/create_legal_consent_tables.php.stub';
        (new \CreateLegalConsentTables())->up();
    }

    public function getRouteByPartialName(string $name, ...$args)
    {
        $prefix = config('legal-consent.routes.prefix');

        if (empty($prefix)) {
            return route("{$name}", ...$args);
        }

        return route("{$prefix}.{$name}", ...$args);
    }
}
