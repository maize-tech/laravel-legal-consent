<?php

namespace Maize\LegalConsent\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maize\LegalConsent\Http\Resources\LegalDocumentResource;
use Maize\LegalConsent\Support\Config;

class LegalDocumentController extends Controller
{
    public function __invoke(Request $request, string $type): LegalDocumentResource
    {
        $document = Config::getFinder()->findForType($type, true);

        return new LegalDocumentResource($document);
    }
}
