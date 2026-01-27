# Phase 7: Search & Category Pages

**Parent**: [plan.md](./plan.md) | **Depends on**: [Phase 6](./phase-06-versions.md)
**Priority**: P2 | **Status**: pending | **Effort**: 1.5h

## Overview

Implement search functionality and category detail pages. Search queries app names and descriptions; categories show filtered app listings.

## Key Insights

- Search form in header submits to /search?q={query}
- Search results similar to apps listing layout
- Category pages show apps in specific category
- Developer profile page (optional) shows apps by developer
- Reuse listing components from Phase 4

## Requirements

1. Implement search controller method
2. Create search results view
3. Implement category detail page
4. Update header search form action
5. Optional: Developer profile page

## Architecture

### Routes
| Route | Controller Method | View |
|-------|-------------------|------|
| GET /search?q={query} | getSearch | search.blade.php |
| GET /category/{slug} | getCategory | category.blade.php |
| GET /developer/{id} | getDeveloper | developer.blade.php (optional) |

### Search Logic
```php
App::where('name', 'like', "%{$query}%")
   ->orWhere('description', 'like', "%{$query}%")
   ->paginate(12);
```

## Related Files

| File | Action | Notes |
|------|--------|-------|
| `views/search.blade.php` | CREATE | Search results page |
| `views/category.blade.php` | CREATE | Category detail page |
| `views/developer.blade.php` | CREATE | Developer profile (optional) |
| `src/Http/Controllers/ApkpureController.php` | UPDATE | Add search, category methods |
| `routes/web.php` | UPDATE | Add search, category routes |
| `partials/header.blade.php` | UPDATE | Set search form action |

## Implementation Steps

### 1. Update Controller - Search

```php
// ApkpureController.php

use Illuminate\Http\Request;

public function getSearch(Request $request)
{
    $query = $request->get('q', '');

    $apps = collect();

    if (strlen($query) >= 2) {
        $apps = App::with(['developer', 'categories'])
            ->where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->latest()
            ->paginate(12);
    }

    $topDownloads = App::with(['categories'])->limit(5)->get();

    Theme::setTitle('Search: ' . $query . ' - APKPure');

    return Theme::scope('search', compact('query', 'apps', 'topDownloads'))->render();
}
```

### 2. Update Controller - Category

```php
public function getCategory($slug)
{
    $category = AppCategory::where('slug', $slug)->firstOrFail();

    $apps = App::with(['developer', 'categories'])
        ->whereHas('categories', fn($q) => $q->where('slug', $slug))
        ->latest()
        ->paginate(12);

    $allCategories = AppCategory::orderBy('name')->get();

    $topDownloads = App::with(['categories'])
        ->whereHas('categories', fn($q) => $q->where('slug', $slug))
        ->limit(5)
        ->get();

    Theme::setTitle($category->name . ' Apps - APKPure');
    Theme::meta('description', $category->description ?? 'Download ' . $category->name . ' apps for Android.');

    return Theme::scope('category', compact('category', 'apps', 'allCategories', 'topDownloads'))->render();
}
```

### 3. Optional - Developer Profile

```php
public function getDeveloper($id)
{
    $developer = Developer::findOrFail($id);

    $apps = App::with(['categories'])
        ->where('developer_id', $id)
        ->latest()
        ->paginate(12);

    Theme::setTitle($developer->name . ' - APKPure');

    return Theme::scope('developer', compact('developer', 'apps'))->render();
}
```

### 4. Update Routes

```php
// routes/web.php
Theme::registerRoutes(function (): void {
    Route::group(['controller' => ApkpureController::class], function (): void {
        Route::get('/', 'getIndex')->name('public.index');
        Route::get('/apps', 'getApps')->name('public.apps');
        Route::get('/games', 'getGames')->name('public.games');
        Route::get('/app/{id}', 'getAppDetail')->name('public.app.detail');
        Route::get('/app/{id}/versions', 'getAppVersions')->name('public.app.versions');
        Route::get('/search', 'getSearch')->name('public.search');
        Route::get('/category/{slug}', 'getCategory')->name('public.category');
        Route::get('/developer/{id}', 'getDeveloper')->name('public.developer');
    });
});
```

### 5. Update Header Search Form

```blade
{{-- partials/header.blade.php --}}
<form class="formsearch" method="get" action="{{ route('public.search') }}">
  <div class="search-input">
    <input type="text"
           id="form_query"
           name="q"
           placeholder="Search for Apps and Games"
           autocomplete="off"
           value="{{ request('q') }}">
    <input class="search-btn-icon" type="submit" value="">
  </div>
</form>
```

### 6. Create views/search.blade.php

```blade
@extends(Theme::getThemeNamespace('layouts.default'))

@section('breadcrumbs')
<div class="breadcrumb-wrap">
  <div class="breadcrumb">
    <a href="{{ route('public.index') }}">Home</a>
    <span class="separator">/</span>
    <span class="current">Search: {{ $query }}</span>
  </div>
</div>
@endsection

@section('content')
  <div class="page-header">
    <h1 class="page-title">Search Results</h1>
    <p class="page-desc">
      @if($apps->count() > 0)
        Found {{ $apps->total() }} results for "{{ $query }}"
      @else
        No results found for "{{ $query }}"
      @endif
    </p>
  </div>

  @if($apps->count() > 0)
  <div class="category-apk-list-box category-module">
    <div class="apk-list-column-three">
      @foreach($apps as $app)
        @include(Theme::getThemeNamespace('partials.app-item'), ['app' => $app])
      @endforeach
    </div>
  </div>

  @if($apps->hasPages())
  <div class="pagination-wrap">
    {{ $apps->withQueryString()->links() }}
  </div>
  @endif
  @else
  <div class="no-results">
    <p>Try different keywords or browse our categories:</p>
    <div class="category-links">
      <a href="{{ route('public.apps') }}" class="filter-item">Apps</a>
      <a href="{{ route('public.games') }}" class="filter-item">Games</a>
    </div>
  </div>
  @endif
@endsection

@section('sidebar')
  @include(Theme::getThemeNamespace('partials.sidebar.apkpure-app-widget'))
  @include(Theme::getThemeNamespace('partials.sidebar.top-downloads'), ['apps' => $topDownloads])
@endsection
```

### 7. Create views/category.blade.php

```blade
@extends(Theme::getThemeNamespace('layouts.default'))

@section('breadcrumbs')
<div class="breadcrumb-wrap">
  <div class="breadcrumb">
    <a href="{{ route('public.index') }}">Home</a>
    <span class="separator">/</span>
    <a href="{{ route('public.apps') }}">Apps</a>
    <span class="separator">/</span>
    <span class="current">{{ $category->name }}</span>
  </div>
</div>
@endsection

@section('content')
  <div class="page-header">
    <h1 class="page-title">{{ $category->name }}</h1>
    @if($category->description)
    <p class="page-desc">{{ $category->description }}</p>
    @endif
  </div>

  {{-- Category Filters --}}
  @include(Theme::getThemeNamespace('partials.category-filters'), [
    'categories' => $allCategories,
    'currentCategory' => $category->slug,
    'baseRoute' => 'public.apps'
  ])

  <div class="category-apk-list-box category-module">
    <div class="category-module-title">
      <span class="title-text">{{ $category->name }} Apps</span>
    </div>
    <div class="apk-list-column-three">
      @forelse($apps as $app)
        @include(Theme::getThemeNamespace('partials.app-item'), ['app' => $app])
      @empty
        <p class="text-muted">No apps in this category yet.</p>
      @endforelse
    </div>
  </div>

  @if($apps->hasPages())
  <div class="pagination-wrap">
    {{ $apps->links() }}
  </div>
  @endif
@endsection

@section('sidebar')
  @include(Theme::getThemeNamespace('partials.sidebar.apkpure-app-widget'))
  @include(Theme::getThemeNamespace('partials.sidebar.top-downloads'), [
    'apps' => $topDownloads,
    'title' => 'Top ' . $category->name
  ])
@endsection
```

### 8. Create views/developer.blade.php (Optional)

```blade
@extends(Theme::getThemeNamespace('layouts.default'))

@section('breadcrumbs')
<div class="breadcrumb-wrap">
  <div class="breadcrumb">
    <a href="{{ route('public.index') }}">Home</a>
    <span class="separator">/</span>
    <span class="current">{{ $developer->name }}</span>
  </div>
</div>
@endsection

@section('content')
  <div class="page-header developer-header">
    @if($developer->logo)
    <img src="{{ RvMedia::getImageUrl($developer->logo) }}" alt="{{ $developer->name }}" class="developer-logo">
    @endif
    <div>
      <h1 class="page-title">{{ $developer->name }}</h1>
      @if($developer->description)
      <p class="page-desc">{{ $developer->description }}</p>
      @endif
    </div>
  </div>

  <div class="category-apk-list-box category-module">
    <div class="category-module-title">
      <span class="title-text">Apps by {{ $developer->name }}</span>
    </div>
    <div class="apk-list-column-three">
      @forelse($apps as $app)
        @include(Theme::getThemeNamespace('partials.app-item'), ['app' => $app])
      @empty
        <p class="text-muted">No apps from this developer.</p>
      @endforelse
    </div>
  </div>

  @if($apps->hasPages())
  <div class="pagination-wrap">
    {{ $apps->links() }}
  </div>
  @endif
@endsection

@section('sidebar')
  @include(Theme::getThemeNamespace('partials.sidebar.apkpure-app-widget'))
@endsection
```

## Todo List

- [ ] Add getSearch method to controller
- [ ] Add getCategory method to controller
- [ ] Add getDeveloper method to controller (optional)
- [ ] Register search, category, developer routes
- [ ] Update header search form action
- [ ] Create views/search.blade.php
- [ ] Create views/category.blade.php
- [ ] Create views/developer.blade.php (optional)
- [ ] Test search with various queries
- [ ] Test category filtering
- [ ] Test pagination with query strings

## Success Criteria

1. Search form submits correctly
2. Search results display matching apps
3. No results message for empty searches
4. Category page shows filtered apps
5. Developer page shows developer apps (optional)
6. Pagination preserves query parameters
7. SEO meta tags set correctly

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| SQL injection via search | Security | Use parameterized queries (Eloquent does this) |
| Slow search on large DB | Poor UX | Add database indexes on name, description |
| Empty search query | Wasted request | Require min 2 characters |
| Category not found | 404 error | Use firstOrFail for proper 404 |

## Next Steps

After completion, proceed to [Phase 8: Widgets & Shortcodes](./phase-08-widgets.md)
