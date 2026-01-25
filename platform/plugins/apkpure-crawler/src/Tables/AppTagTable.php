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
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\ImageColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;
use Wallis\ApkpureCrawler\Models\AppTag;

class AppTagTable extends TableAbstract
{
    public function setup(): void
    {
        $this->defaultSortColumnName = 'created_at';

        $this
            ->model(AppTag::class)
            ->addHeaderAction(CreateHeaderAction::make()->route('apkpure-crawler.app-tags.create'))
            ->addColumns([
                IdColumn::make(),
                ImageColumn::make('logo')->title(trans('plugins/apkpure-crawler::app-tags.form.logo')),
                NameColumn::make()->route('apkpure-crawler.app-tags.edit'),
                CreatedAtColumn::make(),
            ])
            ->addActions([
                EditAction::make()->route('apkpure-crawler.app-tags.edit'),
                DeleteAction::make()->route('apkpure-crawler.app-tags.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('apkpure-crawler.app-tags.destroy'),
            ])
            ->addBulkChanges([
                NameBulkChange::make(),
                CreatedAtBulkChange::make(),
            ])
            ->queryUsing(function (Builder $query): Builder {
                return $query
                    ->with(['slugable'])
                    ->select([
                        'id',
                        'name',
                        'logo',
                        'created_at',
                    ]);
            });
    }
}
