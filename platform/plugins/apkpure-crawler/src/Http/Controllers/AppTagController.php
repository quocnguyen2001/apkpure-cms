<?php

namespace Wallis\ApkpureCrawler\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;
use Wallis\ApkpureCrawler\Forms\AppTagForm;
use Wallis\ApkpureCrawler\Http\Requests\AppTagRequest;
use Wallis\ApkpureCrawler\Models\AppTag;
use Wallis\ApkpureCrawler\Tables\AppTagTable;

class AppTagController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/apkpure-crawler::app-tags.menu_name'), route('apkpure-crawler.app-tags.index'));
    }

    public function index(AppTagTable $dataTable): View|Factory|Response
    {
        $this->pageTitle(trans('plugins/apkpure-crawler::app-tags.menu_name'));

        return $dataTable->renderTable();
    }

    public function create(): string
    {
        $this->pageTitle(trans('plugins/apkpure-crawler::app-tags.create'));

        return AppTagForm::create()->renderForm();
    }

    public function store(AppTagRequest $request): BaseHttpResponse
    {
        $form = AppTagForm::create()->setRequest($request)->save();

        /** @var AppTag $appTag */
        $appTag = $form->getModel();

        return $this
            ->httpResponse()
            ->setPreviousRoute('apkpure-crawler.app-tags.index')
            ->setNextRoute('apkpure-crawler.app-tags.edit', $appTag->getKey())
            ->withCreatedSuccessMessage();
    }

    public function edit(AppTag $appTag): string
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $appTag->name]));

        return AppTagForm::createFromModel($appTag)->renderForm();
    }

    public function update(AppTag $appTag, AppTagRequest $request): BaseHttpResponse
    {
        AppTagForm::createFromModel($appTag)->setRequest($request)->save();

        return $this
            ->httpResponse()
            ->setPreviousRoute('apkpure-crawler.app-tags.index')
            ->withUpdatedSuccessMessage();
    }

    public function destroy(AppTag $appTag): DeleteResourceAction
    {
        return DeleteResourceAction::make($appTag);
    }
}
