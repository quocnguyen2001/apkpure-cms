<?php

namespace Theme\Apkpure\Http\Controllers;

use Botble\Theme\Facades\Theme;
use Botble\Theme\Http\Controllers\PublicController;
use Illuminate\Http\Request;
use Wallis\ApkpureCrawler\Models\App;
use Wallis\ApkpureCrawler\Models\AppCategory;

class ApkpureController extends PublicController
{
    protected const ITEMS_PER_PAGE = 18;

    public function getApps()
    {
        $apps = App::query()
            ->with(['developer', 'categories'])
            ->whereDoesntHave('categories', fn ($q) => $q->where('name', 'Games'))
            ->latest()
            ->paginate(self::ITEMS_PER_PAGE);

        Theme::breadcrumb()->add(__('Home'), route('public.index'))->add(__('Apps'));

        return Theme::scope('apps', compact('apps'))->render();
    }

    public function getGames()
    {
        $games = App::query()
            ->with(['developer', 'categories'])
            ->whereHas('categories', fn ($q) => $q->where('name', 'Games'))
            ->latest()
            ->paginate(self::ITEMS_PER_PAGE);

        Theme::breadcrumb()->add(__('Home'), route('public.index'))->add(__('Games'));

        return Theme::scope('games', ['apps' => $games])->render();
    }

    public function getSearch(Request $request)
    {
        $query = $request->input('q', '');
        // Escape LIKE wildcards to prevent SQL injection
        $escapedQuery = str_replace(['%', '_'], ['\%', '\_'], $query);

        $apps = App::query()
            ->with(['developer', 'categories'])
            ->when($escapedQuery, fn ($q) => $q->where('name', 'like', "%{$escapedQuery}%"))
            ->latest()
            ->paginate(self::ITEMS_PER_PAGE);

        Theme::breadcrumb()->add(__('Home'), route('public.index'))->add(__('Search'));

        return Theme::scope('search', compact('apps', 'query'))->render();
    }

    public function getAppDetail(string $slug)
    {
        $app = App::query()
            ->with(['developer', 'categories', 'tags', 'lastedVersion', 'versions' => fn ($q) => $q->latest()->limit(5)])
            ->where('slug', $slug)
            ->firstOrFail();

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add($app->categories->first()?->name ?? __('Apps'), route('public.apps'))
            ->add($app->name);

        return Theme::scope('app-detail', compact('app'))->render();
    }

    public function getAppVersions(string $slug)
    {
        $app = App::query()
            ->with(['developer', 'versions' => fn ($q) => $q->latest()])
            ->where('slug', $slug)
            ->firstOrFail();

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add($app->name, route('public.app.detail', $app->slug))
            ->add(__('Versions'));

        return Theme::scope('app-versions', compact('app'))->render();
    }

    public function getCategory(string $slug)
    {
        $category = AppCategory::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $apps = App::query()
            ->with(['developer', 'categories'])
            ->whereHas('categories', fn ($q) => $q->where('ac_app_categories.id', $category->id))
            ->latest()
            ->paginate(self::ITEMS_PER_PAGE);

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add($category->name);

        return Theme::scope('category', compact('apps', 'category'))->render();
    }
}
