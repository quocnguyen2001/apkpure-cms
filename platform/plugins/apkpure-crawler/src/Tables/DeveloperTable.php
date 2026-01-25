<?php

namespace Wallis\ApkpureCrawler\Tables;

use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\BulkChanges\CreatedAtBulkChange;
use Botble\Table\BulkChanges\NameBulkChange;
use Botble\Table\BulkChanges\StatusBulkChange;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\ImageColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;
use Wallis\ApkpureCrawler\Models\Developer;

class DeveloperTable extends TableAbstract
{
    public function setup(): void
    {
        $this->defaultSortColumnName = 'created_at';

        $this
            ->model(Developer::class)
            ->addHeaderAction(CreateHeaderAction::make()->route('apkpure-crawler.developers.create'))
            ->addColumns([
                IdColumn::make(),
                ImageColumn::make('logo')->title(trans('plugins/apkpure-crawler::developers.form.logo')),
                NameColumn::make()->route('apkpure-crawler.developers.edit'),
                Column::make('website')->title(trans('plugins/apkpure-crawler::developers.form.website')),
                CreatedAtColumn::make(),
                StatusColumn::make(),
            ])
            ->addActions([
                EditAction::make()->route('apkpure-crawler.developers.edit'),
                DeleteAction::make()->route('apkpure-crawler.developers.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('apkpure-crawler.developers.destroy'),
            ])
            ->addBulkChanges([
                NameBulkChange::make(),
                StatusBulkChange::make(),
                CreatedAtBulkChange::make(),
            ])
            ->queryUsing(function (Builder $query): Builder {
                return $query
                    ->select([
                        'id',
                        'name',
                        'logo',
                        'website',
                        'created_at',
                        'status',
                    ]);
            });
    }
}
