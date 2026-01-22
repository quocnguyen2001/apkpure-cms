<?php

namespace Wallis\ApkpureCrawler\Http\Requests;

use Botble\Support\Http\Requests\Request;

class ExtractRequest extends Request
{
    public function rules(): array
    {
        return [
            'selectors' => ['required', 'array'],
            'selectors.*' => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'selectors.required' => 'Selectors are required.',
            'selectors.array' => 'Selectors must be an array.',
            'selectors.*.string' => 'Each selector must be a string.',
        ];
    }

    public function attributes(): array
    {
        return [
            'selectors' => 'selectors',
        ];
    }
}
