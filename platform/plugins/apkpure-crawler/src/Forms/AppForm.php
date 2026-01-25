<?php

namespace Wallis\ApkpureCrawler\Forms;

use Botble\Base\Forms\FieldOptions\ContentFieldOption;
use Botble\Base\Forms\FieldOptions\DatePickerFieldOption;
use Botble\Base\Forms\FieldOptions\DescriptionFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImagesFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\DatePickerField;
use Botble\Base\Forms\Fields\EditorField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\MediaImagesField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Table\TableBuilder;
use Wallis\ApkpureCrawler\Enums\AppPlatformEnum;
use Wallis\ApkpureCrawler\Http\Requests\AppRequest;
use Wallis\ApkpureCrawler\Models\App;
use Wallis\ApkpureCrawler\Models\AppCategory;
use Wallis\ApkpureCrawler\Models\AppTag;
use Wallis\ApkpureCrawler\Models\Developer;
use Wallis\ApkpureCrawler\Tables\AppVersionTable;

class AppForm extends FormAbstract
{
    public function __construct(protected TableBuilder $tableBuilder)
    {
        parent::__construct();
    }

    public function setup(): void
    {
        $this
            ->model(App::class)
            ->setValidatorClass(AppRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->required())
            ->add('description', TextareaField::class, DescriptionFieldOption::make())
            ->add('content', EditorField::class, ContentFieldOption::make()->allowedShortcodes())
            ->add(
                'platform',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/apkpure-crawler::apps.form.platform'))
                    ->choices(AppPlatformEnum::labels())
                    ->required()
                    ->searchable()
                    ->emptyValue(trans('core/base::forms.select_placeholder'))
            )
            ->add(
                'developer_id',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/apkpure-crawler::apps.form.developer'))
                    ->choices(fn () => Developer::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->emptyValue(trans('core/base::forms.select_placeholder'))
                    ->allowClear()
            )
            ->add(
                'requires_android_os',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/apkpure-crawler::apps.form.requires_android_os'))
                    ->maxLength(120)
            )
            ->add(
                'lasted_update',
                DatePickerField::class,
                DatePickerFieldOption::make()
                    ->label(trans('plugins/apkpure-crawler::apps.form.lasted_update'))
                    ->withTimePicker(false)
                    ->defaultValue(null)
            )
            ->add(
                'google_play',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/apkpure-crawler::apps.form.google_play'))
                    ->maxLength(255)
            )
            ->add(
                'images',
                MediaImagesField::class,
                MediaImagesFieldOption::make()
                    ->values($this->getModel()->images)
                    ->label(trans('plugins/apkpure-crawler::apps.form.images'))
            )
            ->add(
                'logo',
                MediaImageField::class,
                MediaImageFieldOption::make()->label(trans('plugins/apkpure-crawler::apps.form.logo'))
            )
            ->add(
                'categories[]',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/apkpure-crawler::apps.form.categories'))
                    ->choices(fn () => AppCategory::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->multiple()
                    ->allowClear()
                    ->emptyValue(trans('core/base::forms.select_placeholder'))
                    ->when($this->getModel()->getKey(), function (SelectFieldOption $fieldOption): SelectFieldOption {
                        return $fieldOption->selected($this->getModel()->categories()->pluck('ac_app_categories.id')->all());
                    })
            )
            ->add(
                'tags[]',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/apkpure-crawler::apps.form.tags'))
                    ->choices(fn () => AppTag::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->multiple()
                    ->allowClear()
                    ->emptyValue(trans('core/base::forms.select_placeholder'))
                    ->when($this->getModel()->getKey(), function (SelectFieldOption $fieldOption): SelectFieldOption {
                        return $fieldOption->selected($this->getModel()->tags()->pluck('ac_app_tags.id')->all());
                    })
            )
            ->setBreakFieldPoint('logo')
            ->when($this->model->id, function (): void {
                $this->addMetaBoxes([
                    'app-versions' => [
                        'title' => trans('plugins/apkpure-crawler::app-versions.menu_name'),
                        'content' => $this->tableBuilder->create(AppVersionTable::class)
                            ->setAjaxUrl(route('apkpure-crawler.app-versions.index', $this->getModel()->id ?: 0))
                            ->renderTable([
                                'app_id' => $this->getModel()->getKey(),
                            ]),
                        'header_actions' => view('plugins/apkpure-crawler::app-versions.partials.header-actions', [
                            'app' => $this->getModel(),
                        ])->render(),
                        'has_table' => true,
                    ],
                ]);
            });
    }
}
