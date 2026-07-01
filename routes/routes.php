<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Maize\LegalConsent\Http\Controllers\LegalConsentController;
use Maize\LegalConsent\Http\Controllers\LegalDocumentController;
use Maize\LegalConsent\Http\Controllers\WithdrawLegalConsentController;
use Maize\LegalConsent\Support\Config;

if (Config::routesEnabled()) {
    Route::group([
        'prefix' => Config::getRoutePrefix(),
        'as' => Str::finish(Config::getRouteName(), '.'),
        'middleware' => Config::getRouteMiddleware(),
    ], function () {

        Route::get('documents/{type}', LegalDocumentController::class)
            ->name('documents.show')
            ->middleware(Config::getShowMiddleware());

        Route::post('documents/{document}', LegalConsentController::class)
            ->name('documents.consent')
            ->middleware(Config::getConsentMiddleware());

        Route::delete('documents/{document}', WithdrawLegalConsentController::class)
            ->name('documents.withdraw')
            ->middleware(Config::getWithdrawMiddleware());
    });
}
