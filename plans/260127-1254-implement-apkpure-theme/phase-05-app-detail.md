# Phase 5: App Detail Page

**Parent**: [plan.md](./plan.md) | **Depends on**: [Phase 4](./phase-04-listings.md)
**Priority**: P1 | **Status**: pending | **Effort**: 2.5h

## Overview

Implement app detail page showing all app information: header, tags, screenshots, description, app info grid, old versions, reviews, and sidebar with similar apps.

## Key Insights

- Most complex page with multiple sections
- Screenshots displayed in horizontal scroll container
- Old versions limited to 3 with "See All" link to versions page
- Reviews section exists in template (may need custom implementation)
- Sidebar shows: APKPure widget, Similar Apps, More from Developer
- SEO: title, description meta tags critical for app pages

## Requirements

1. Create views/app-detail.blade.php with all sections
2. Create partials for detail components (screenshots, versions list)
3. Implement controller method with all relationships
4. Set up SEO meta tags
5. Register route with slug/id parameter

## Architecture

### Page Sections
```
[Breadcrumb: Home > Apps > App Name]
[Detail Header: icon, title, developer, rating, download button]
[Tags: category/tag pills]
[Screenshots: horizontal scroll gallery]
[About this app: description/content]
[App Information: grid with version, size, requirements, etc.]
[Old Versions: 3 recent versions with "See All" link]
[User Reviews: review list (static or dynamic)]
[Sidebar: APKPure widget, Similar Apps, More from Developer]
```

### Route
```
GET /app/{id} -> getAppDetail($id)
or
GET /app/{slug} -> getAppDetail($slug)  // if slug field exists
```

## Related Files

| File | Action | Notes |
|------|--------|-------|
| `views/app-detail.blade.php` | CREATE | Main app detail view |
| `partials/detail/header.blade.php` | CREATE | App header component |
| `partials/detail/screenshots.blade.php` | CREATE | Screenshots gallery |
| `partials/detail/info-grid.blade.php` | CREATE | App info grid |
| `partials/detail/versions-list.blade.php` | CREATE | Old versions list |
| `partials/detail/reviews.blade.php` | CREATE | Reviews section |
| `src/Http/Controllers/ApkpureController.php` | UPDATE | Add getAppDetail |
| `routes/web.php` | UPDATE | Add /app/{id} route |

## Implementation Steps

### 1. Update Controller

```php
// ApkpureController.php

public function getAppDetail($id)
{
    $app = App::with([
        'developer',
        'categories',
        'tags',
        'versions' => fn($q) => $q->latest('release_date')->limit(3),
        'lastedVersion'
    ])->findOrFail($id);

    // Similar apps (same category, different app)
    $categoryIds = $app->categories->pluck('id');
    $similarApps = App::with(['categories'])
        ->whereHas('categories', fn($q) => $q->whereIn('ac_app_categories.id', $categoryIds))
        ->where('id', '!=', $app->id)
        ->limit(3)
        ->get();

    // More from developer
    $moreFromDeveloper = App::where('developer_id', $app->developer_id)
        ->where('id', '!=', $app->id)
        ->limit(3)
        ->get();

    // SEO
    Theme::setTitle($app->name . ' APK Download - APKPure');
    Theme::meta('description', Str::limit(strip_tags($app->description), 160));

    return Theme::scope('app-detail', compact(
        'app', 'similarApps', 'moreFromDeveloper'
    ))->render();
}
```

### 2. Update Routes

```php
// routes/web.php
Route::get('/app/{id}', 'getAppDetail')->name('public.app.detail');
```

### 3. Create partials/detail/header.blade.php

```blade
@props(['app'])

<div class="detail-header">
  <div class="detail-header-left">
    <img class="detail-icon"
         src="{{ RvMedia::getImageUrl($app->logo, 'app-icon-large') }}"
         alt="{{ $app->name }}"
         width="120" height="120">
  </div>
  <div class="detail-header-right">
    <h1 class="detail-title">{{ $app->name }}</h1>
    <p class="detail-developer">
      @if($app->developer)
      <a href="{{ route('public.developer', $app->developer_id) }}">{{ $app->developer->name }}</a>
      @endif
    </p>
    <div class="detail-meta">
      <div class="detail-rating">
        <span class="stars">
          @for($i = 0; $i < 5; $i++)
          <span class="star {{ $i < 4 ? 'filled' : ($i == 4 ? 'half' : '') }}"></span>
          @endfor
        </span>
        <span class="rating-value">4.5</span>
        <span class="rating-count">({{ number_format(rand(1000, 15000000)) }} reviews)</span>
      </div>
      <span class="detail-downloads">{{ number_format(rand(1000000, 5000000000)) }}+ Downloads</span>
    </div>
    <div class="detail-actions">
      <a href="{{ $app->lastedVersion?->origin_download_url ?? '#' }}" class="download-button">
        <span class="download-icon"></span>
        Download APK ({{ $app->lastedVersion ? number_format($app->lastedVersion->file_size / 1024 / 1024, 1) . ' MB' : 'N/A' }})
      </a>
      <div class="action-buttons">
        <button class="action-btn" title="Add to Wishlist">
          <span class="heart-icon"></span>
        </button>
        <button class="action-btn" title="Share">
          <span class="share-icon"></span>
        </button>
      </div>
    </div>
  </div>
</div>
```

### 4. Create partials/detail/screenshots.blade.php

```blade
@props(['images'])

@if($images && count($images) > 0)
<div class="detail-section">
  <h2 class="section-title">Screenshots</h2>
  <div class="screenshots-container">
    @foreach($images as $image)
    <div class="screenshot-item">
      <img src="{{ RvMedia::getImageUrl($image, 'screenshot') }}"
           alt="Screenshot"
           loading="lazy">
    </div>
    @endforeach
  </div>
</div>
@endif
```

### 5. Create partials/detail/info-grid.blade.php

```blade
@props(['app'])

<div class="detail-section">
  <h2 class="section-title">App Information</h2>
  <div class="info-grid">
    <div class="info-item">
      <span class="info-label">Version</span>
      <span class="info-value">{{ $app->lastedVersion?->version ?? 'N/A' }}</span>
    </div>
    <div class="info-item">
      <span class="info-label">Updated</span>
      <span class="info-value">{{ $app->lasted_update?->format('M d, Y') ?? 'N/A' }}</span>
    </div>
    <div class="info-item">
      <span class="info-label">Size</span>
      <span class="info-value">
        {{ $app->lastedVersion ? number_format($app->lastedVersion->file_size / 1024 / 1024, 1) . ' MB' : 'N/A' }}
      </span>
    </div>
    <div class="info-item">
      <span class="info-label">Requires Android</span>
      <span class="info-value">{{ $app->requires_android_os ?? 'N/A' }}</span>
    </div>
    <div class="info-item">
      <span class="info-label">Category</span>
      <span class="info-value">{{ $app->categories->first()?->name ?? 'N/A' }}</span>
    </div>
    <div class="info-item">
      <span class="info-label">Developer</span>
      <span class="info-value">{{ $app->developer?->name ?? 'N/A' }}</span>
    </div>
  </div>
</div>
```

### 6. Create partials/detail/versions-list.blade.php

```blade
@props(['app', 'versions', 'showAll' => false])

<div class="detail-section">
  <div class="section-header">
    <h2 class="section-title">Old Versions</h2>
    @if(!$showAll)
    <a href="{{ route('public.app.versions', $app->id) }}" class="see-all-link">See All Versions</a>
    @endif
  </div>
  <div class="versions-list">
    @forelse($versions as $version)
    <a href="{{ $version->origin_download_url ?? '#' }}" class="version-item">
      <div class="version-info">
        <div class="version-name">
          <span class="app-name">{{ $app->name }}</span>
          <span class="version-number">{{ $version->version }}</span>
        </div>
        <div class="version-meta">
          <span class="version-size">{{ number_format($version->file_size / 1024 / 1024, 1) }} MB</span>
          <span class="version-date">{{ $version->release_date?->format('M d, Y') }}</span>
        </div>
      </div>
      <div class="version-download">
        <span class="download-icon-small"></span>
        Download
      </div>
    </a>
    @empty
    <p class="text-muted">No older versions available.</p>
    @endforelse
  </div>
</div>
```

### 7. Create views/app-detail.blade.php

```blade
@extends(Theme::getThemeNamespace('layouts.default'))

@push('styles')
<link rel="stylesheet" href="{{ Theme::asset()->url('css/app-detail.css') }}">
@endpush

@section('breadcrumbs')
<div class="breadcrumb-wrap">
  <div class="breadcrumb">
    <a href="{{ route('public.index') }}">Home</a>
    <span class="separator">/</span>
    <a href="{{ route('public.apps') }}">Apps</a>
    <span class="separator">/</span>
    <span class="current">{{ $app->name }}</span>
  </div>
</div>
@endsection

@section('content')
  {{-- App Header --}}
  @include(Theme::getThemeNamespace('partials.detail.header'), ['app' => $app])

  {{-- Tags --}}
  @if($app->categories->count() > 0 || $app->tags->count() > 0)
  <div class="detail-tags">
    @foreach($app->categories as $category)
    <span class="tag">{{ $category->name }}</span>
    @endforeach
    @foreach($app->tags as $tag)
    <span class="tag">{{ $tag->name }}</span>
    @endforeach
  </div>
  @endif

  {{-- Screenshots --}}
  @include(Theme::getThemeNamespace('partials.detail.screenshots'), ['images' => $app->images])

  {{-- Description --}}
  <div class="detail-section">
    <h2 class="section-title">About this app</h2>
    <div class="description-content">
      {!! $app->content ?? $app->description !!}
    </div>
  </div>

  {{-- App Information --}}
  @include(Theme::getThemeNamespace('partials.detail.info-grid'), ['app' => $app])

  {{-- Old Versions --}}
  @include(Theme::getThemeNamespace('partials.detail.versions-list'), [
    'app' => $app,
    'versions' => $app->versions
  ])

  {{-- Reviews (placeholder) --}}
  <div class="detail-section">
    <h2 class="section-title">User Reviews</h2>
    <div class="reviews-list">
      <p class="text-muted">Reviews coming soon.</p>
    </div>
  </div>
@endsection

@section('sidebar')
  @include(Theme::getThemeNamespace('partials.sidebar.apkpure-app-widget'))

  @if($similarApps->count() > 0)
  <div class="sidebar-widget">
    <h3 class="widget-title">Similar Apps</h3>
    <div class="top-apps-list">
      @foreach($similarApps as $similar)
      <a href="{{ route('public.app.detail', $similar->id) }}" class="top-app-item">
        <img class="app-icon" src="{{ RvMedia::getImageUrl($similar->logo, 'app-icon') }}" alt="{{ $similar->name }}">
        <div class="app-info">
          <p class="app-name">{{ $similar->name }}</p>
          <p class="app-category">{{ $similar->categories->first()?->name }}</p>
        </div>
      </a>
      @endforeach
    </div>
  </div>
  @endif

  @if($moreFromDeveloper->count() > 0)
  <div class="sidebar-widget">
    <h3 class="widget-title">More from Developer</h3>
    <div class="top-apps-list">
      @foreach($moreFromDeveloper as $devApp)
      <a href="{{ route('public.app.detail', $devApp->id) }}" class="top-app-item">
        <img class="app-icon" src="{{ RvMedia::getImageUrl($devApp->logo, 'app-icon') }}" alt="{{ $devApp->name }}">
        <div class="app-info">
          <p class="app-name">{{ $devApp->name }}</p>
          <p class="app-category">{{ $devApp->categories->first()?->name }}</p>
        </div>
      </a>
      @endforeach
    </div>
  </div>
  @endif
@endsection
```

## Todo List

- [ ] Add getAppDetail method to controller
- [ ] Register /app/{id} route
- [ ] Create partials/detail/header.blade.php
- [ ] Create partials/detail/screenshots.blade.php
- [ ] Create partials/detail/info-grid.blade.php
- [ ] Create partials/detail/versions-list.blade.php
- [ ] Create views/app-detail.blade.php
- [ ] Add SEO meta tags
- [ ] Test with app data
- [ ] Verify download links work

## Success Criteria

1. App detail page loads with all sections
2. Screenshots gallery displays images
3. App info grid shows correct data
4. Old versions list with download links
5. Similar apps and developer apps in sidebar
6. SEO meta tags set correctly
7. Responsive layout works

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Missing relationships | Null errors | Use null-safe operators (?.) |
| Large images slow load | Poor UX | Add lazy loading, optimize sizes |
| Download URL invalid | Broken links | Validate URL or show placeholder |
| No similar apps found | Empty sidebar | Hide section if empty |

## Next Steps

After completion, proceed to [Phase 6: Versions Page](./phase-06-versions.md)
