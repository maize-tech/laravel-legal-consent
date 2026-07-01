<?php

namespace Maize\LegalConsent\Enums;

enum DocumentStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
