<?php

namespace Wallis\ApkpureCrawler\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;
use Wallis\ApkpureCrawler\Forms\AppCategoryForm;
use Wallis\ApkpureCrawler\Http\Requests\AppCategoryRequest;
use Wallis\ApkpureCrawler\Models\AppCategory;
use Wallis\ApkpureCrawler\Tables\AppCategoryTable;

class AppCategoryController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/apkpure-crawler::app-categories.menu_name'), route('apkpure-crawler.app-categories.index'));
    }

    public function index(AppCategoryTable $dataTable): View|Factory|Response
    {
        $this->pageTitle(trans('plugins/apkpure-crawler::app-categories.menu_name'));

        return $dataTable->renderTable();
    }

    public function create(): string
    {
        $this->pageTitle(trans('plugins/apkpure-crawler::app-categories.create'));

        return AppCategoryForm::create()->renderForm();
    }

    public function store(AppCategoryRequest $request): BaseHttpResponse
    {
        $form = AppCategoryForm::create()->setRequest($request)->save();

        /** @var AppCategory $appCategory */
        $appCategory = $form->getModel();

        return $this
            ->httpResponse()
            ->setPreviousRoute('apkpure-crawler.app-categories.index')
            ->setNextRoute('apkpure-crawler.app-categories.edit', $appCategory->getKey())
            ->withCreatedSuccessMessage();
    }

    public function edit(AppCategory $appCategory): string
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $appCategory->name]));

        return AppCategoryForm::createFromModel($appCategory)->renderForm();
    }

    public function update(AppCategory $appCategory, AppCategoryRequest $request): BaseHttpResponse
    {
        AppCategoryForm::createFromModel($appCategory)->setRequest($request)->save();

        return $this
            ->httpResponse()
            ->setPreviousRoute('apkpure-crawler.app-categories.index')
            ->withUpdatedSuccessMessage();
    }

    public function destroy(AppCategory $appCategory): DeleteResourceAction
    {
        return DeleteResourceAction::make($appCategory);
    }
}
