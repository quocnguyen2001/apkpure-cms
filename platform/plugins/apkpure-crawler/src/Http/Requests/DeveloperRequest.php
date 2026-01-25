<?php

namespace Wallis\ApkpureCrawler\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Rules\MediaImageRule;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class DeveloperRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'string', new MediaImageRule()],
            'description' => ['nullable', 'string', 'max:10000'],
            'content' => ['nullable', 'string', 'max:300000'],
            'status' => ['required', Rule::in(BaseStatusEnum::values())],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => trans('plugins/apkpure-crawler::developers.form.name'),
            'website' => trans('plugins/apkpure-crawler::developers.form.website'),
            'logo' => trans('plugins/apkpure-crawler::developers.form.logo'),
            'description' => trans('plugins/apkpure-crawler::developers.form.description'),
            'content' => trans('plugins/apkpure-crawler::developers.form.content'),
            'status' => trans('plugins/apkpure-crawler::developers.form.status'),
        ];
    }
}
