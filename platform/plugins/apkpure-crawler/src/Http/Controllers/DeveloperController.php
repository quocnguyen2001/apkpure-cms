<?php

namespace Wallis\ApkpureCrawler\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;
use Wallis\ApkpureCrawler\Forms\DeveloperForm;
use Wallis\ApkpureCrawler\Http\Requests\DeveloperRequest;
use Wallis\ApkpureCrawler\Models\Developer;
use Wallis\ApkpureCrawler\Tables\DeveloperTable;

class DeveloperController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/apkpure-crawler::developers.menu_name'), route('apkpure-crawler.developers.index'));
    }

    public function index(DeveloperTable $dataTable): View|Factory|Response
    {
        $this->pageTitle(trans('plugins/apkpure-crawler::developers.menu_name'));

        return $dataTable->renderTable();
    }

    public function create(): string
    {
        $this->pageTitle(trans('plugins/apkpure-crawler::developers.create'));

        return DeveloperForm::create()->renderForm();
    }

    public function store(DeveloperRequest $request): BaseHttpResponse
    {
        $form = DeveloperForm::create()->setRequest($request)->save();

        /** @var Developer $developer */
        $developer = $form->getModel();

        return $this
            ->httpResponse()
            ->setPreviousRoute('apkpure-crawler.developers.index')
            ->setNextRoute('apkpure-crawler.developers.edit', $developer->getKey())
            ->withCreatedSuccessMessage();
    }

    public function edit(Developer $developer): string
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $developer->name]));

        return DeveloperForm::createFromModel($developer)->renderForm();
    }

    public function update(Developer $developer, DeveloperRequest $request): BaseHttpResponse
    {
        DeveloperForm::createFromModel($developer)->setRequest($request)->save();

        return $this
            ->httpResponse()
            ->setPreviousRoute('apkpure-crawler.developers.index')
            ->withUpdatedSuccessMessage();
    }

    public function destroy(Developer $developer): DeleteResourceAction
    {
        return DeleteResourceAction::make($developer);
    }
}
