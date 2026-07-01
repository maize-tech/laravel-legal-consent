<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Maize\LegalConsent\Http\Controllers\LegalConsentController;
use Maize\LegalConsent\Http\Controllers\LegalDocumentController;

if (config('legal-consent.routes.enabled')) {
    $prefix = config('legal-consent.routes.prefix');
    $middleware = config('legal-consent.routes.middleware');
    $name = config('legal-consent.routes.name');

    Route::group([
        'prefix' => $prefix,
        'as' => Str::finish($name, '.'),
        'middleware' => $middleware,
    ], function () {

        Route::get('documents/{type}', LegalDocumentController::class)
            ->name('documents.show')
            ->middleware(
                config('legal-consent.routes.endpoints.show.middleware')
            );

        Route::post('documents/{document}', LegalConsentController::class)
            ->name('documents.consent')
            ->middleware(
                config('legal-consent.routes.endpoints.consent.middleware')
            );
    });
}
