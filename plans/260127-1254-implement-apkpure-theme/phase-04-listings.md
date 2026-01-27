# Phase 4: Apps/Games Listing Pages

**Parent**: [plan.md](./plan.md) | **Depends on**: [Phase 3](./phase-03-homepage.md)
**Priority**: P1 | **Status**: pending | **Effort**: 2h

## Overview

Implement apps and games listing pages with category filters, pagination, and shared listing component.

## Key Insights

- Apps and Games pages share same layout, differ only by platform filter
- Category filters shown as horizontal pill buttons
- App list uses same `.apk-list-column-three` grid as homepage
- Two sections: "Popular Apps/Games" and "New & Updated"
- URL structure: `/apps`, `/games`, `/apps?category=social`

## Requirements

1. Create views/apps.blade.php for apps listing
2. Create views/games.blade.php for games listing
3. Implement category filtering via query parameter
4. Add pagination
5. Create controller methods for both pages

## Architecture

### URL Routes
| Route | Controller Method | View |
|-------|-------------------|------|
| GET /apps | getApps | apps.blade.php |
| GET /games | getGames | games.blade.php |
| GET /apps?category={slug} | getApps | apps.blade.php (filtered) |

### Query Parameters
- `category`: Filter by category slug
- `page`: Pagination page number
- `sort`: Sort order (newest, popular) - future enhancement

## Related Files

| File | Action | Notes |
|------|--------|-------|
| `views/apps.blade.php` | CREATE | Apps listing page |
| `views/games.blade.php` | CREATE | Games listing page |
| `partials/category-filters.blade.php` | CREATE | Filter pills component |
| `src/Http/Controllers/ApkpureController.php` | UPDATE | Add getApps, getGames |
| `routes/web.php` | UPDATE | Add /apps, /games routes |

## Implementation Steps

### 1. Update Controller

```php
// ApkpureController.php

public function getApps(Request $request)
{
    $categorySlug = $request->get('category');

    $query = App::with(['developer', 'categories'])
        ->where('platform', AppPlatformEnum::APP);

    if ($categorySlug) {
        $query->whereHas('categories', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    $apps = $query->latest()->paginate(12);

    $categories = AppCategory::orderBy('name')->get();
    $currentCategory = $categorySlug;

    $topDownloads = App::with(['categories'])
        ->where('platform', AppPlatformEnum::APP)
        ->limit(5)
        ->get();

    return Theme::scope('apps', compact('apps', 'categories', 'currentCategory', 'topDownloads'))->render();
}

public function getGames(Request $request)
{
    $categorySlug = $request->get('category');

    // Filter by Games category
    $query = App::with(['developer', 'categories'])
        ->whereHas('categories', fn($q) => $q->where('name', 'Games'));

    // Additional category filter (e.g., Action, Arcade)
    if ($categorySlug) {
        $query->whereHas('categories', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    $games = $query->latest()->paginate(12);

    // Get game subcategories (Action, Arcade, etc.)
    $categories = AppCategory::whereIn('name', ['Action', 'Arcade'])->orderBy('name')->get();
    $currentCategory = $categorySlug;

    $topDownloads = App::with(['categories'])
        ->whereHas('categories', fn($q) => $q->where('name', 'Games'))
        ->limit(5)
        ->get();

    return Theme::scope('games', compact('games', 'categories', 'currentCategory', 'topDownloads'))->render();
}
```

### 2. Update Routes

```php
// routes/web.php
Theme::registerRoutes(function (): void {
    Route::group(['controller' => ApkpureController::class], function (): void {
        Route::get('/', 'getIndex')->name('public.index');
        Route::get('/apps', 'getApps')->name('public.apps');
        Route::get('/games', 'getGames')->name('public.games');
    });
});
```

### 3. Create partials/category-filters.blade.php

```blade
@props(['categories', 'currentCategory' => null, 'baseRoute'])

<div class="category-filters">
  <a href="{{ route($baseRoute) }}"
     class="filter-item {{ !$currentCategory ? 'active' : '' }}">
    All
  </a>
  @foreach($categories as $category)
  <a href="{{ route($baseRoute, ['category' => $category->slug]) }}"
     class="filter-item {{ $currentCategory === $category->slug ? 'active' : '' }}">
    {{ $category->name }}
  </a>
  @endforeach
</div>
```

### 4. Create views/apps.blade.php

```blade
@extends(Theme::getThemeNamespace('layouts.default'))

@section('breadcrumbs')
<div class="breadcrumb-wrap">
  <div class="breadcrumb">
    <a href="{{ route('public.index') }}">Home</a>
    <span class="separator">/</span>
    <span class="current">Apps</span>
  </div>
</div>
@endsection

@section('content')
  {{-- Page Header --}}
  <div class="page-header">
    <h1 class="page-title">Android Apps</h1>
    <p class="page-desc">Download free Android apps APK files</p>
  </div>

  {{-- Category Filters --}}
  @include(Theme::getThemeNamespace('partials.category-filters'), [
    'categories' => $categories,
    'currentCategory' => $currentCategory,
    'baseRoute' => 'public.apps'
  ])

  {{-- Apps List --}}
  <div class="category-apk-list-box category-module">
    <div class="category-module-title">
      <span class="title-text">
        {{ $currentCategory ? ucfirst($currentCategory) . ' Apps' : 'Popular Apps' }}
      </span>
    </div>
    <div class="apk-list-column-three">
      @forelse($apps as $app)
        @include(Theme::getThemeNamespace('partials.app-item'), ['app' => $app])
      @empty
        <p class="text-muted">No apps found.</p>
      @endforelse
    </div>
  </div>

  {{-- Pagination --}}
  @if($apps->hasPages())
  <div class="pagination-wrap">
    {{ $apps->withQueryString()->links() }}
  </div>
  @endif
@endsection

@section('sidebar')
  @include(Theme::getThemeNamespace('partials.sidebar.apkpure-app-widget'))
  @include(Theme::getThemeNamespace('partials.sidebar.top-downloads'), [
    'apps' => $topDownloads,
    'title' => 'Top Downloads'
  ])
@endsection
```

### 5. Create views/games.blade.php

```blade
@extends(Theme::getThemeNamespace('layouts.default'))

@section('breadcrumbs')
<div class="breadcrumb-wrap">
  <div class="breadcrumb">
    <a href="{{ route('public.index') }}">Home</a>
    <span class="separator">/</span>
    <span class="current">Games</span>
  </div>
</div>
@endsection

@section('content')
  {{-- Page Header --}}
  <div class="page-header">
    <h1 class="page-title">Android Games</h1>
    <p class="page-desc">Download free Android games APK files</p>
  </div>

  {{-- Category Filters --}}
  @include(Theme::getThemeNamespace('partials.category-filters'), [
    'categories' => $categories,
    'currentCategory' => $currentCategory,
    'baseRoute' => 'public.games'
  ])

  {{-- Games List --}}
  <div class="category-apk-list-box category-module">
    <div class="category-module-title">
      <span class="title-text">
        {{ $currentCategory ? ucfirst($currentCategory) . ' Games' : 'Popular Games' }}
      </span>
    </div>
    <div class="apk-list-column-three">
      @forelse($games as $game)
        @include(Theme::getThemeNamespace('partials.app-item'), ['app' => $game])
      @empty
        <p class="text-muted">No games found.</p>
      @endforelse
    </div>
  </div>

  {{-- Pagination --}}
  @if($games->hasPages())
  <div class="pagination-wrap">
    {{ $games->withQueryString()->links() }}
  </div>
  @endif
@endsection

@section('sidebar')
  @include(Theme::getThemeNamespace('partials.sidebar.apkpure-app-widget'))
  @include(Theme::getThemeNamespace('partials.sidebar.top-downloads'), [
    'apps' => $topDownloads,
    'title' => 'Top Games'
  ])
@endsection
```

### 6. Add Pagination Styles

```css
/* Add to main.css or create pagination partial */
.pagination-wrap {
  margin-top: 24px;
  display: flex;
  justify-content: center;
}

.pagination {
  display: flex;
  gap: 8px;
}

.pagination .page-link {
  padding: 8px 12px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  color: var(--text-secondary);
}

.pagination .page-item.active .page-link {
  background-color: var(--primary-green);
  border-color: var(--primary-green);
  color: #fff;
}
```

## Todo List

- [ ] Add getApps method to controller
- [ ] Add getGames method to controller
- [ ] Register /apps and /games routes
- [ ] Create partials/category-filters.blade.php
- [ ] Create views/apps.blade.php
- [ ] Create views/games.blade.php
- [ ] Add pagination styles
- [ ] Test category filtering
- [ ] Test pagination

## Success Criteria

1. /apps page loads with app data
2. /games page loads with game data
3. Category filter pills work correctly
4. Active filter highlighted
5. Pagination works with query string preserved
6. Empty state shown when no results

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Slow query with many apps | Page timeout | Add eager loading, caching |
| Category slug not found | No results | Handle gracefully, show all |
| Pagination style mismatch | Broken UI | Override Laravel pagination views |

## Next Steps

After completion, proceed to [Phase 5: App Detail Page](./phase-05-app-detail.md)
