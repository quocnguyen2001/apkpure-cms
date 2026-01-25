<?php

namespace Wallis\ApkpureCrawler\Forms;

use Botble\Base\Forms\FieldOptions\ContentFieldOption;
use Botble\Base\Forms\FieldOptions\DescriptionFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\EditorField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Wallis\ApkpureCrawler\Http\Requests\DeveloperRequest;
use Wallis\ApkpureCrawler\Models\Developer;

class DeveloperForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(Developer::class)
            ->setValidatorClass(DeveloperRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->required())
            ->add(
                'website',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/apkpure-crawler::developers.form.website'))
                    ->maxLength(255)
            )
            ->add(
                'logo',
                MediaImageField::class,
                MediaImageFieldOption::make()->label(trans('plugins/apkpure-crawler::developers.form.logo'))
            )
            ->add('description', TextareaField::class, DescriptionFieldOption::make())
            ->add('content', EditorField::class, ContentFieldOption::make()->allowedShortcodes())
            ->add('status', SelectField::class, StatusFieldOption::make())
            ->setBreakFieldPoint('status');
    }
}
