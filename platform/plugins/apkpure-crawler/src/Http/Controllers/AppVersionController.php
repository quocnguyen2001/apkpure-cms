<?php

namespace Wallis\ApkpureCrawler\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;
use Wallis\ApkpureCrawler\Forms\AppVersionForm;
use Wallis\ApkpureCrawler\Http\Requests\AppVersionRequest;
use Wallis\ApkpureCrawler\Models\App;
use Wallis\ApkpureCrawler\Models\AppVersion;
use Wallis\ApkpureCrawler\Tables\AppVersionTable;

class AppVersionController extends BaseController
{
    public function index(AppVersionTable $dataTable): View|Factory|Response
    {
        return $dataTable->renderTable();
    }

    public function create(): BaseHttpResponse
    {
        $form = AppVersionForm::create()
            ->setUseInlineJs(true)
            ->renderForm();

        return $this
            ->httpResponse()
            ->setData([
                'title' => trans('plugins/apkpure-crawler::app-versions.create'),
                'content' => $form,
            ]);
    }

    public function store(AppVersionRequest $request): BaseHttpResponse
    {
        $app = App::query()->findOrFail($request->input('app_id'));

        $request->merge([
            'storage_disk' => $request->input(
                'storage_disk',
                config('plugins.apkpure-crawler.scraper.storage.disk', 'local')
            ),
            'scrape_ref_id' => $request->input('scrape_ref_id', $app->scrape_ref_id ?? 0),
        ]);

        AppVersionForm::create()->setRequest($request)->save();

        return $this
            ->httpResponse()
            ->withCreatedSuccessMessage();
    }

    public function edit(int|string $id): BaseHttpResponse
    {
        $appVersion = AppVersion::query()->findOrFail($id);

        $form = AppVersionForm::createFromModel($appVersion)
            ->setUseInlineJs(true)
            ->renderForm();

        return $this
            ->httpResponse()
            ->setData([
                'title' => trans('plugins/apkpure-crawler::app-versions.edit', ['version' => $appVersion->version]),
                'content' => $form,
            ]);
    }

    public function update(int|string $id, AppVersionRequest $request): BaseHttpResponse
    {
        $appVersion = AppVersion::query()->findOrFail($id);

        AppVersionForm::createFromModel($appVersion)
            ->setRequest($request)
            ->save();

        return $this
            ->httpResponse()
            ->withUpdatedSuccessMessage();
    }

    public function destroy(int|string $id): DeleteResourceAction
    {
        $appVersion = AppVersion::query()->findOrFail($id);

        return DeleteResourceAction::make($appVersion);
    }
}
