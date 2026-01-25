<x-core::button
    tag="a"
    data-bs-toggle="modal"
    data-bs-target="#app-version-modal"
    :href="route('apkpure-crawler.app-versions.create', ['app_id' => BaseHelper::stringify($app->id)])"
    icon="ti ti-plus"
>
    {{ trans('plugins/apkpure-crawler::app-versions.add_new') }}
</x-core::button>
