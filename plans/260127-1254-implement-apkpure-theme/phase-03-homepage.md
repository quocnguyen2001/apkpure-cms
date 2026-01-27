# Phase 3: Homepage Implementation

**Parent**: [plan.md](./plan.md) | **Depends on**: [Phase 2](./phase-02-layouts.md)
**Priority**: P1 | **Status**: pending | **Effort**: 2.5h

## Overview

Implement homepage (index.blade.php) with slide banner, quick access icons, trending apps, popular games, new releases sections, and right sidebar widgets.

## Key Insights

- Homepage has 5 main sections: banner, quick access, trending, games, new releases
- Each section uses `.category-apk-list-box` with `.apk-list-column-three` grid
- App items share common structure - create reusable partial
- Right sidebar has 3 widgets: APKPure App, Top Downloads, Trending Games
- Data comes from App model; filter by platform/category for games vs apps

## Requirements

1. Create views/index.blade.php with all homepage sections
2. Create partials/app-item.blade.php for reusable app card
3. Create partials/slide-banner.blade.php for hero carousel
4. Create partials/quick-access.blade.php for category icons
5. Implement data queries in controller or view composers

## Architecture

### Homepage Sections
```
[Slide Banner - Featured apps carousel]
[Quick Access - Category icons row]
[Trending Now - 6 apps in 3-column grid]
[Popular Games - 6 games in 3-column grid]
[New Releases - 3 apps in 3-column grid]
```

### Data Requirements
| Section | Query | Limit |
|---------|-------|-------|
| Slide Banner | Admin-selected or latest apps | 4 |
| Trending Now | Apps NOT in "Games" category, latest | 6 |
| Popular Games | Apps with "Games" category | 6 |
| New Releases | All apps ordered by created_at desc | 3 |
| Top Downloads | Apps ordered by total downloads | 5 |
| Trending Games | Apps with "Games" category, latest | 3 |

**Note:** Games are filtered by category, not platform. Use `whereHas('categories', fn($q) => $q->where('name', 'Games'))`

## Related Files

| File | Action | Notes |
|------|--------|-------|
| `views/index.blade.php` | REWRITE | Full homepage implementation |
| `partials/app-item.blade.php` | CREATE | Reusable app card component |
| `partials/slide-banner.blade.php` | CREATE | Hero carousel |
| `partials/quick-access.blade.php` | CREATE | Category quick links |
| `src/Http/Controllers/ApkpureController.php` | UPDATE | Add getIndex method |

## Implementation Steps

### 1. Create Controller Method

```php
// ApkpureController.php
public function getIndex()
{
    // Apps NOT in Games category
    $trendingApps = App::with(['developer', 'categories'])
        ->whereDoesntHave('categories', fn($q) => $q->where('name', 'Games'))
        ->latest()
        ->limit(6)
        ->get();

    // Apps IN Games category
    $popularGames = App::with(['developer', 'categories'])
        ->whereHas('categories', fn($q) => $q->where('name', 'Games'))
        ->latest()
        ->limit(6)
        ->get();

    $newReleases = App::with(['developer', 'categories'])
        ->latest()
        ->limit(3)
        ->get();

    // Banner apps from theme options or latest
    $bannerAppIds = json_decode(theme_option('banner_app_ids', '[]'));
    $bannerApps = $bannerAppIds
        ? App::whereIn('id', $bannerAppIds)->get()
        : App::latest()->limit(4)->get();

    $topDownloads = App::with(['categories'])
        ->latest()
        ->limit(5)
        ->get();

    $trendingGames = App::with(['categories'])
        ->whereHas('categories', fn($q) => $q->where('name', 'Games'))
        ->latest()
        ->limit(3)
        ->get();

    return Theme::scope('index', compact(
        'trendingApps', 'popularGames', 'newReleases',
        'bannerApps', 'topDownloads', 'trendingGames'
    ))->render();
}
```

### 2. Register Route

```php
// routes/web.php
Theme::registerRoutes(function (): void {
    Route::group(['controller' => ApkpureController::class], function (): void {
        Route::get('/', 'getIndex')->name('public.index');
    });
});
```

### 3. Create partials/app-item.blade.php

```blade
@props(['app', 'showRating' => true])

<a href="{{ route('public.app.detail', $app->id) }}" class="apk-item">
  <div class="apk">
    <img class="apk-icon"
         src="{{ RvMedia::getImageUrl($app->logo, 'app-icon') }}"
         alt="{{ $app->name }}"
         width="72" height="72">
    <div class="apk-text-box">
      <p class="apk-title">{{ $app->name }}</p>
      <p class="apk-developer">{{ $app->developer?->name }}</p>
      @if($showRating)
      <div class="apk-score">
        <span class="star"></span>
        <span class="rating">4.5</span>
      </div>
      @endif
    </div>
  </div>
</a>
```

### 4. Create partials/slide-banner.blade.php

```blade
@props(['apps'])

<div id="top-slide-banner" class="slide-banner">
  <div class="container">
    <div class="list">
      @foreach($apps as $app)
      <a title="{{ $app->name }} APK" class="banner-item" href="{{ route('public.app.detail', $app->id) }}">
        <img class="banner-bg"
             alt="{{ $app->name }}"
             src="{{ RvMedia::getImageUrl($app->images[0] ?? $app->logo, 'banner') }}"
             width="868" height="170">
        <div class="mask"></div>
        <div class="info">
          <img class="icon" alt="{{ $app->name }}" src="{{ RvMedia::getImageUrl($app->logo) }}" width="32" height="32">
          <div class="name">{{ $app->name }}</div>
          <div class="button">Download</div>
        </div>
      </a>
      @endforeach
    </div>
  </div>
  <ul class="dots">
    @foreach($apps as $index => $app)
    <li class="{{ $index === 0 ? 'on' : '' }}">{{ $index + 1 }}</li>
    @endforeach
  </ul>
  <div class="prev"></div>
  <div class="next"></div>
</div>
```

### 5. Create partials/quick-access.blade.php

```blade
<div class="module quick-access-new">
  <a title="Games" href="{{ route('public.games') }}" class="quick-item">
    <i class="icon icon-games"></i>
    <p>Games</p>
  </a>
  <a title="Apps" href="{{ route('public.apps') }}" class="quick-item">
    <i class="icon icon-apps"></i>
    <p>Apps</p>
  </a>
  <a title="News" href="#" class="quick-item">
    <i class="icon icon-news"></i>
    <p>News</p>
  </a>
  {{-- Add more items as needed --}}
</div>
```

### 6. Create views/index.blade.php

```blade
@extends(Theme::getThemeNamespace('layouts.default'))

@section('content')
  {{-- Slide Banner --}}
  @include(Theme::getThemeNamespace('partials.slide-banner'), ['apps' => $bannerApps])

  {{-- Quick Access --}}
  @include(Theme::getThemeNamespace('partials.quick-access'))

  {{-- Trending Apps --}}
  <div class="category-apk-list-box category-module">
    <div class="category-module-title">
      <span class="title-text">Trending Now</span>
      <a href="{{ route('public.apps') }}" class="see-more">See All</a>
    </div>
    <div class="apk-list-column-three">
      @foreach($trendingApps as $app)
        @include(Theme::getThemeNamespace('partials.app-item'), ['app' => $app])
      @endforeach
    </div>
  </div>

  {{-- Popular Games --}}
  <div class="category-apk-list-box category-module">
    <div class="category-module-title">
      <span class="title-text">Popular Games</span>
      <a href="{{ route('public.games') }}" class="see-more">See All</a>
    </div>
    <div class="apk-list-column-three">
      @foreach($popularGames as $app)
        @include(Theme::getThemeNamespace('partials.app-item'), ['app' => $app])
      @endforeach
    </div>
  </div>

  {{-- New Releases --}}
  <div class="category-apk-list-box category-module">
    <div class="category-module-title">
      <span class="title-text">New Releases</span>
      <a href="{{ route('public.apps') }}" class="see-more">See All</a>
    </div>
    <div class="apk-list-column-three">
      @foreach($newReleases as $app)
        @include(Theme::getThemeNamespace('partials.app-item'), ['app' => $app])
      @endforeach
    </div>
  </div>
@endsection

@section('sidebar')
  @include(Theme::getThemeNamespace('partials.sidebar.apkpure-app-widget'))
  @include(Theme::getThemeNamespace('partials.sidebar.top-downloads'), ['apps' => $topDownloads])
  @include(Theme::getThemeNamespace('partials.sidebar.trending-games'), ['games' => $trendingGames])
@endsection
```

## Todo List

- [ ] Update ApkpureController with getIndex method
- [ ] Register homepage route in routes/web.php
- [ ] Create partials/app-item.blade.php
- [ ] Create partials/slide-banner.blade.php
- [ ] Create partials/quick-access.blade.php
- [ ] Rewrite views/index.blade.php
- [ ] Add JavaScript for banner carousel (if not using library)
- [ ] Test with sample app data

## Success Criteria

1. Homepage loads without errors
2. Banner carousel displays apps
3. All 3 app sections show data
4. Sidebar widgets render with data
5. Links to apps/games pages work
6. Responsive layout works

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| No app data in database | Empty sections | Add "No apps found" fallback |
| Route name conflicts | 404 errors | Use unique route names with public. prefix |
| Banner JS not working | Static banner | Implement simple vanilla JS carousel |

## Next Steps

After completion, proceed to [Phase 4: Apps/Games Listing](./phase-04-listings.md)
