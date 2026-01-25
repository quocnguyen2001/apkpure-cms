<?php

namespace Wallis\ApkpureCrawler\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\DateColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Wallis\ApkpureCrawler\Models\AppVersion;

class AppVersionTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(AppVersion::class)
            ->setView('plugins/apkpure-crawler::app-versions.items')
            ->setDom($this->simpleDom())
            ->addColumns([
                IdColumn::make(),
                FormattedColumn::make('version')
                    ->title(trans('plugins/apkpure-crawler::app-versions.form.version'))
                    ->alignStart()
                    ->getValueUsing(function (FormattedColumn $column) {
                        $item = $column->getItem();
                        $version = BaseHelper::clean($item->version);

                        if (! $this->hasPermission('apkpure-crawler.app-versions.edit')) {
                            return $version;
                        }

                        return $version ? Html::link(route('apkpure-crawler.app-versions.edit', $item->getKey()), $version, [
                            'data-bs-toggle' => 'modal',
                            'data-bs-target' => '#app-version-modal',
                        ]) : '&mdash;';
                    }),
                DateColumn::make('release_date')
                    ->title(trans('plugins/apkpure-crawler::app-versions.form.release_date'))
                    ->dateFormat('Y-m-d'),
                Column::make('file_path')
                    ->title(trans('plugins/apkpure-crawler::app-versions.form.file')),
                CreatedAtColumn::make(),
            ])
            ->addActions([
                EditAction::make()
                    ->route('apkpure-crawler.app-versions.edit')
                    ->attributes([
                        'data-bs-toggle' => 'modal',
                        'data-bs-target' => '#app-version-modal',
                    ])
                    ->permission('apkpure-crawler.app-versions.edit'),
                DeleteAction::make()
                    ->route('apkpure-crawler.app-versions.destroy')
                    ->permission('apkpure-crawler.app-versions.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('apkpure-crawler.app-versions.destroy'),
            ])
            ->queryUsing(function (Builder $query) {
                return $query
                    ->select([
                        'id',
                        'app_id',
                        'version',
                        'release_date',
                        'file_path',
                        'created_at',
                    ])
                    ->where('app_id', request()->route()->parameter('id'))
                    ->latest('release_date');
            });
    }
}
