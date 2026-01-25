<?php

namespace Wallis\ApkpureCrawler\Http\Requests;

use Botble\Base\Rules\MediaImageRule;
use Botble\Support\Http\Requests\Request;

class AppTagRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'string', new MediaImageRule()],
            'description' => ['nullable', 'string', 'max:10000'],
            'content' => ['nullable', 'string', 'max:300000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => trans('plugins/apkpure-crawler::app-tags.form.name'),
            'logo' => trans('plugins/apkpure-crawler::app-tags.form.logo'),
            'description' => trans('plugins/apkpure-crawler::app-tags.form.description'),
            'content' => trans('plugins/apkpure-crawler::app-tags.form.content'),
        ];
    }
}
