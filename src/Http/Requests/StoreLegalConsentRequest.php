<?php

namespace Maize\LegalConsent\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLegalConsentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'locale' => ['sometimes', 'nullable', 'string', 'max:10'],
        ];
    }
}
