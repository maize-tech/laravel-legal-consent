<?php

namespace Maize\LegalConsent\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maize\LegalConsent\Http\Resources\LegalDocumentResource;

class LegalDocumentController extends Controller
{
    public function __invoke(Request $request, string $type)
    {
        $finderClass = config('legal-consent.legal_document_finder');
        $document = app($finderClass)->findForType($type, true);

        return new LegalDocumentResource($document);
    }
}
