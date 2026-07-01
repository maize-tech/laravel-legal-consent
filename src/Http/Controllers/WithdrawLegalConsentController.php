<?php

namespace Maize\LegalConsent\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Maize\LegalConsent\Contracts\LegalConsenter;
use Maize\LegalConsent\Models\LegalDocument;

class WithdrawLegalConsentController extends Controller
{
    public function __invoke(Request $request, LegalDocument $document): Response
    {
        $user = $request->user();

        abort_unless($user instanceof LegalConsenter, Response::HTTP_UNAUTHORIZED);

        $user->withdrawLegalDocument($document);

        return response()->noContent();
    }
}
