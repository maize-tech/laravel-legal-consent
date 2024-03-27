<?php

namespace Maize\LegalConsent\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Maize\LegalConsent\LegalConsentServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
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
        config()->set('database.default', 'testing');

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

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });

        $migration = include __DIR__.'/../database/migrations/create_legal_consent_tables.php.stub';
        $migration->up();
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
