<?php

namespace Wallis\ApkpureCrawler\Tables;

use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\BulkChanges\CreatedAtBulkChange;
use Botble\Table\BulkChanges\NameBulkChange;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\ImageColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;
use Wallis\ApkpureCrawler\Models\AppCategory;

class AppCategoryTable extends TableAbstract
{
    public function setup(): void
    {
        $this->defaultSortColumnName = 'created_at';

        $this
            ->model(AppCategory::class)
            ->addHeaderAction(CreateHeaderAction::make()->route('apkpure-crawler.app-categories.create'))
            ->addColumns([
                IdColumn::make(),
                ImageColumn::make('logo')->title(trans('plugins/apkpure-crawler::app-categories.form.logo')),
                NameColumn::make()->route('apkpure-crawler.app-categories.edit'),
                FormattedColumn::make('parent_id')
                    ->title(trans('plugins/apkpure-crawler::app-categories.form.parent'))
                    ->getValueUsing(function (FormattedColumn $column) {
                        return $column->getItem()->parent?->name;
                    })
                    ->withEmptyState(),
                CreatedAtColumn::make(),
            ])
            ->addActions([
                EditAction::make()->route('apkpure-crawler.app-categories.edit'),
                DeleteAction::make()->route('apkpure-crawler.app-categories.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('apkpure-crawler.app-categories.destroy'),
            ])
            ->addBulkChanges([
                NameBulkChange::make(),
                CreatedAtBulkChange::make(),
            ])
            ->queryUsing(function (Builder $query): Builder {
                return $query
                    ->with(['parent', 'slugable'])
                    ->select([
                        'id',
                        'name',
                        'parent_id',
                        'logo',
                        'created_at',
                    ]);
            });
    }
}
