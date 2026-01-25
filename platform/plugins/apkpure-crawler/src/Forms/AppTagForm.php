<?php

namespace Wallis\ApkpureCrawler\Forms;

use Botble\Base\Forms\FieldOptions\ContentFieldOption;
use Botble\Base\Forms\FieldOptions\DescriptionFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\Fields\EditorField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Wallis\ApkpureCrawler\Http\Requests\AppTagRequest;
use Wallis\ApkpureCrawler\Models\AppTag;

class AppTagForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(AppTag::class)
            ->setValidatorClass(AppTagRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->required())
            ->add(
                'logo',
                MediaImageField::class,
                MediaImageFieldOption::make()->label(trans('plugins/apkpure-crawler::app-tags.form.logo'))
            )
            ->add('description', TextareaField::class, DescriptionFieldOption::make())
            ->add('content', EditorField::class, ContentFieldOption::make()->allowedShortcodes())
            ->setBreakFieldPoint('logo');
    }
}
