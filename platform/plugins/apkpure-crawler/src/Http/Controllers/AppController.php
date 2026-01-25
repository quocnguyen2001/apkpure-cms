<?php

namespace Wallis\ApkpureCrawler\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;
use Wallis\ApkpureCrawler\Forms\AppForm;
use Wallis\ApkpureCrawler\Http\Requests\AppRequest;
use Wallis\ApkpureCrawler\Models\App;
use Wallis\ApkpureCrawler\Tables\AppTable;

class AppController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/apkpure-crawler::apps.menu_name'), route('apkpure-crawler.apps.index'));
    }

    public function index(AppTable $dataTable): View|Factory|Response
    {
        $this->pageTitle(trans('plugins/apkpure-crawler::apps.menu_name'));

        return $dataTable->renderTable();
    }

    public function create(): string
    {
        $this->pageTitle(trans('plugins/apkpure-crawler::apps.create'));

        return AppForm::create()->renderForm();
    }

    public function store(AppRequest $request): BaseHttpResponse
    {
        $form = AppForm::create()->setRequest($request)->save();

        /** @var App $app */
        $app = $form->getModel();

        $this->syncRelations($app, $request);

        return $this
            ->httpResponse()
            ->setPreviousRoute('apkpure-crawler.apps.index')
            ->setNextRoute('apkpure-crawler.apps.edit', $app->getKey())
            ->withCreatedSuccessMessage();
    }

    public function edit(App $app): string
    {
        Assets::addScriptsDirectly('vendor/core/plugins/apkpure-crawler/js/app-versions-admin.js');

        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $app->name]));

        return AppForm::createFromModel($app)->renderForm();
    }

    public function update(App $app, AppRequest $request): BaseHttpResponse
    {
        $form = AppForm::createFromModel($app)->setRequest($request)->save();

        /** @var App $app */
        $app = $form->getModel();

        $this->syncRelations($app, $request);

        return $this
            ->httpResponse()
            ->setPreviousRoute('apkpure-crawler.apps.index')
            ->withUpdatedSuccessMessage();
    }

    public function destroy(App $app): DeleteResourceAction
    {
        return DeleteResourceAction::make($app);
    }

    protected function syncRelations(App $app, AppRequest $request): void
    {
        $categories = $request->input('categories', []);
        $tags = $request->input('tags', []);

        $app->categories()->sync(is_array($categories) ? $categories : []);
        $app->tags()->sync(is_array($tags) ? $tags : []);
    }
}
