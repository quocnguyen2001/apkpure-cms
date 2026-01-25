<?php

namespace Wallis\ApkpureCrawler\Http\Requests;

use Botble\Base\Rules\MediaImageRule;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;
use Wallis\ApkpureCrawler\Enums\AppPlatformEnum;
use Wallis\ApkpureCrawler\Models\AppCategory;
use Wallis\ApkpureCrawler\Models\AppTag;
use Wallis\ApkpureCrawler\Models\Developer;

class AppRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'string', new MediaImageRule()],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'string', new MediaImageRule()],
            'description' => ['nullable', 'string', 'max:10000'],
            'content' => ['nullable', 'string', 'max:300000'],
            'requires_android_os' => ['nullable', 'string', 'max:120'],
            'lasted_update' => ['nullable', 'date'],
            'platform' => ['required', 'string', Rule::in(AppPlatformEnum::values())],
            'google_play' => ['nullable', 'string', 'max:255'],
            'developer_id' => ['required', Rule::exists((new Developer())->getTable(), 'id')],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['nullable', Rule::exists((new AppCategory())->getTable(), 'id')],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['nullable', Rule::exists((new AppTag())->getTable(), 'id')],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => trans('plugins/apkpure-crawler::apps.form.name'),
            'logo' => trans('plugins/apkpure-crawler::apps.form.logo'),
            'images' => trans('plugins/apkpure-crawler::apps.form.images'),
            'description' => trans('plugins/apkpure-crawler::apps.form.description'),
            'content' => trans('plugins/apkpure-crawler::apps.form.content'),
            'requires_android_os' => trans('plugins/apkpure-crawler::apps.form.requires_android_os'),
            'lasted_update' => trans('plugins/apkpure-crawler::apps.form.lasted_update'),
            'platform' => trans('plugins/apkpure-crawler::apps.form.platform'),
            'google_play' => trans('plugins/apkpure-crawler::apps.form.google_play'),
            'developer_id' => trans('plugins/apkpure-crawler::apps.form.developer'),
            'categories' => trans('plugins/apkpure-crawler::apps.form.categories'),
            'tags' => trans('plugins/apkpure-crawler::apps.form.tags'),
        ];
    }
}
