<?php

namespace Wallis\ApkpureCrawler\Http\Requests;

use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;
use Wallis\ApkpureCrawler\Models\App;

class AppVersionRequest extends Request
{
    public function rules(): array
    {
        return [
            'app_id' => ['required', Rule::exists((new App())->getTable(), 'id')],
            'version' => ['required', 'string', 'max:255'],
            'changelog' => ['nullable', 'string', 'max:300000'],
            'release_date' => ['required', 'date'],
            'file_path' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'app_id' => trans('plugins/apkpure-crawler::app-versions.form.app'),
            'version' => trans('plugins/apkpure-crawler::app-versions.form.version'),
            'changelog' => trans('plugins/apkpure-crawler::app-versions.form.changelog'),
            'release_date' => trans('plugins/apkpure-crawler::app-versions.form.release_date'),
            'file_path' => trans('plugins/apkpure-crawler::app-versions.form.file'),
        ];
    }
}
