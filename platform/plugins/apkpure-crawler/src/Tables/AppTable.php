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
use Botble\Table\Columns\EnumColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\ImageColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;
use Wallis\ApkpureCrawler\Models\App;

class AppTable extends TableAbstract
{
    public function setup(): void
    {
        $this->defaultSortColumnName = 'created_at';

        $this
            ->model(App::class)
            ->addHeaderAction(CreateHeaderAction::make()->route('apkpure-crawler.apps.create'))
            ->addColumns([
                IdColumn::make(),
                ImageColumn::make('logo')->title(trans('plugins/apkpure-crawler::apps.form.logo')),
                NameColumn::make()->route('apkpure-crawler.apps.edit'),
                EnumColumn::make('platform')
                    ->title(trans('plugins/apkpure-crawler::apps.form.platform')),
                FormattedColumn::make('developer_id')
                    ->title(trans('plugins/apkpure-crawler::apps.form.developer'))
                    ->getValueUsing(function (FormattedColumn $column) {
                        return $column->getItem()->developer?->name;
                    })
                    ->withEmptyState(),
                FormattedColumn::make('lasted_update')
                    ->title(trans('plugins/apkpure-crawler::apps.form.lasted_update'))
                    ->getValueUsing(function (FormattedColumn $column) {
                        $lastedUpdate = $column->getItem()->lasted_update;

                        return $lastedUpdate?->toDateString();
                    })
                    ->withEmptyState(),
                CreatedAtColumn::make(),
            ])
            ->addActions([
                EditAction::make()->route('apkpure-crawler.apps.edit'),
                DeleteAction::make()->route('apkpure-crawler.apps.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('apkpure-crawler.apps.destroy'),
            ])
            ->addBulkChanges([
                NameBulkChange::make(),
                CreatedAtBulkChange::make(),
            ])
            ->queryUsing(function (Builder $query): Builder {
                return $query
                    ->with(['developer'])
                    ->select([
                        'id',
                        'name',
                        'logo',
                        'platform',
                        'developer_id',
                        'lasted_update',
                        'created_at',
                    ]);
            });
    }
}
