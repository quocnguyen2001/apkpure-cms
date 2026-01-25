<?php

namespace Wallis\ApkpureCrawler\Forms;

use Botble\Base\Forms\FieldOptions\DatePickerFieldOption;
use Botble\Base\Forms\FieldOptions\MediaFileFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\DatePickerField;
use Botble\Base\Forms\Fields\MediaFileField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Wallis\ApkpureCrawler\Http\Requests\AppVersionRequest;
use Wallis\ApkpureCrawler\Models\AppVersion;

class AppVersionForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(AppVersion::class)
            ->setValidatorClass(AppVersionRequest::class)
            ->contentOnly()
            ->add('app_id', 'hidden', [
                'value' => $this->getRequest()->input('app_id') ?: $this->getModel()->app_id,
            ])
            ->add(
                'version',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/apkpure-crawler::app-versions.form.version'))
                    ->required()
                    ->maxLength(255)
            )
            ->add(
                'release_date',
                DatePickerField::class,
                DatePickerFieldOption::make()
                    ->label(trans('plugins/apkpure-crawler::app-versions.form.release_date'))
                    ->required()
                    ->withTimePicker(false)
            )
            ->add(
                'file_path',
                MediaFileField::class,
                MediaFileFieldOption::make()
                    ->label(trans('plugins/apkpure-crawler::app-versions.form.file'))
                    ->required()
            )
            ->add(
                'changelog',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label(trans('plugins/apkpure-crawler::app-versions.form.changelog'))
                    ->rows(6)
            );
    }
}
