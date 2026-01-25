<?php

namespace Wallis\ApkpureCrawler\Http\Requests;

use Botble\Base\Rules\MediaImageRule;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;
use Wallis\ApkpureCrawler\Models\AppCategory;

class AppCategoryRequest extends Request
{
    public function rules(): array
    {
        $appCategoryId = $this->route('app_category')?->getKey();

        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists((new AppCategory())->getTable(), 'id'),
                Rule::when($appCategoryId, fn () => Rule::notIn([$appCategoryId])),
            ],
            'logo' => ['nullable', 'string', new MediaImageRule()],
            'description' => ['nullable', 'string', 'max:10000'],
            'content' => ['nullable', 'string', 'max:300000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => trans('plugins/apkpure-crawler::app-categories.form.name'),
            'parent_id' => trans('plugins/apkpure-crawler::app-categories.form.parent'),
            'logo' => trans('plugins/apkpure-crawler::app-categories.form.logo'),
            'description' => trans('plugins/apkpure-crawler::app-categories.form.description'),
            'content' => trans('plugins/apkpure-crawler::app-categories.form.content'),
        ];
    }
}
