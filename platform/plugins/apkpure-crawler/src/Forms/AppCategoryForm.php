<?php

namespace Wallis\ApkpureCrawler\Forms;

use Botble\Base\Forms\FieldOptions\ContentFieldOption;
use Botble\Base\Forms\FieldOptions\DescriptionFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\Fields\EditorField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Wallis\ApkpureCrawler\Http\Requests\AppCategoryRequest;
use Wallis\ApkpureCrawler\Models\AppCategory;

class AppCategoryForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(AppCategory::class)
            ->setValidatorClass(AppCategoryRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->required())
            ->add(
                'parent_id',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/apkpure-crawler::app-categories.form.parent'))
                    ->choices(function () {
                        $query = AppCategory::query()->orderBy('name');

                        if ($this->getModel()->getKey()) {
                            $query->where('id', '!=', $this->getModel()->getKey());
                        }

                        return $query->pluck('name', 'id')->all();
                    })
                    ->searchable()
                    ->emptyValue(trans('core/base::forms.select_placeholder'))
                    ->allowClear()
            )
            ->add(
                'logo',
                MediaImageField::class,
                MediaImageFieldOption::make()->label(trans('plugins/apkpure-crawler::app-categories.form.logo'))
            )
            ->add('description', TextareaField::class, DescriptionFieldOption::make())
            ->add('content', EditorField::class, ContentFieldOption::make()->allowedShortcodes())
            ->setBreakFieldPoint('logo');
    }
}
