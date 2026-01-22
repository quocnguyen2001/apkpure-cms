<?php

namespace Wallis\ApkpureCrawler\Http\Requests;

use Botble\Support\Http\Requests\Request;

class ScrapeRequest extends Request
{
    public function rules(): array
    {
        return [
            'url' => ['required', 'url'],
            'async' => ['sometimes', 'boolean'],
            'options' => ['sometimes', 'array'],
            'options.timeout' => ['sometimes', 'integer', 'min:1000', 'max:120000'],
            'options.screenshot' => ['sometimes', 'boolean'],
            'options.waitForSelector' => ['sometimes', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'url.required' => 'URL is required.',
            'url.url' => 'URL must be a valid URL.',
            'options.timeout.integer' => 'Timeout must be an integer.',
            'options.timeout.min' => 'Timeout must be at least 1000 milliseconds.',
            'options.timeout.max' => 'Timeout may not be greater than 120000 milliseconds.',
            'options.waitForSelector.string' => 'Wait for selector must be a string.',
        ];
    }

    public function attributes(): array
    {
        return [
            'url' => 'URL',
            'options' => 'options',
            'options.timeout' => 'timeout',
            'options.screenshot' => 'screenshot',
            'options.waitForSelector' => 'wait for selector',
        ];
    }
}
