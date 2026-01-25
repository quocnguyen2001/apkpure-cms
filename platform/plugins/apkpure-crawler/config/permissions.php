<?php

return [
    [
        'name' => 'Apps',
        'flag' => 'apkpure-crawler.apps.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'apkpure-crawler.apps.create',
        'parent_flag' => 'apkpure-crawler.apps.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'apkpure-crawler.apps.edit',
        'parent_flag' => 'apkpure-crawler.apps.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'apkpure-crawler.apps.destroy',
        'parent_flag' => 'apkpure-crawler.apps.index',
    ],
    [
        'name' => 'App versions',
        'flag' => 'apkpure-crawler.app-versions.index',
        'parent_flag' => 'apkpure-crawler.apps.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'apkpure-crawler.app-versions.create',
        'parent_flag' => 'apkpure-crawler.app-versions.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'apkpure-crawler.app-versions.edit',
        'parent_flag' => 'apkpure-crawler.app-versions.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'apkpure-crawler.app-versions.destroy',
        'parent_flag' => 'apkpure-crawler.app-versions.index',
    ],
    [
        'name' => 'App categories',
        'flag' => 'apkpure-crawler.app-categories.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'apkpure-crawler.app-categories.create',
        'parent_flag' => 'apkpure-crawler.app-categories.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'apkpure-crawler.app-categories.edit',
        'parent_flag' => 'apkpure-crawler.app-categories.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'apkpure-crawler.app-categories.destroy',
        'parent_flag' => 'apkpure-crawler.app-categories.index',
    ],
    [
        'name' => 'App tags',
        'flag' => 'apkpure-crawler.app-tags.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'apkpure-crawler.app-tags.create',
        'parent_flag' => 'apkpure-crawler.app-tags.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'apkpure-crawler.app-tags.edit',
        'parent_flag' => 'apkpure-crawler.app-tags.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'apkpure-crawler.app-tags.destroy',
        'parent_flag' => 'apkpure-crawler.app-tags.index',
    ],
    [
        'name' => 'Developers',
        'flag' => 'apkpure-crawler.developers.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'apkpure-crawler.developers.create',
        'parent_flag' => 'apkpure-crawler.developers.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'apkpure-crawler.developers.edit',
        'parent_flag' => 'apkpure-crawler.developers.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'apkpure-crawler.developers.destroy',
        'parent_flag' => 'apkpure-crawler.developers.index',
    ],
];
