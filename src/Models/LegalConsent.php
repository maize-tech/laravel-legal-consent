<?php

namespace Maize\LegalConsent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class LegalConsent extends Pivot
{
    use HasFactory;

    protected $table = 'legal_consents';

    protected $fillable = [
        'document_id',
        'user_type',
        'user_id',
    ];
}
