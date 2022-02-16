<?php

namespace Maize\LegalConsent\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maize\LegalConsent\Models\LegalDocument;

class LegalConsentController extends Controller
{
    public function __invoke(Request $request, LegalDocument $document)
    {
        $request
            ->user()
            ->acceptLegalDocument($document);

        return response()->noContent();
    }
}
