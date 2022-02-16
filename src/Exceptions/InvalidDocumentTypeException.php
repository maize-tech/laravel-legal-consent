<?php

namespace Maize\LegalConsent\Exceptions;

use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidDocumentTypeException extends HttpException
{
    public function __construct(string $message = 'The document type is invalid.')
    {
        parent::__construct(Response::HTTP_BAD_REQUEST, $message);
    }
}
