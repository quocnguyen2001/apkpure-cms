# Phase 6: App Versions Page

**Parent**: [plan.md](./plan.md) | **Depends on**: [Phase 5](./phase-05-app-detail.md)
**Priority**: P2 | **Status**: pending | **Effort**: 1.5h

## Overview

Implement dedicated versions page showing all historical versions of an app with load more pagination.

## Key Insights

- Mini app header at top (icon, name, developer)
- "Download Latest" button prominent
- Full versions list with version number, size, date
- Load more via pagination or AJAX (simpler: pagination)
- Sidebar shows: APKPure widget, App Info, Similar Apps

## Requirements

1. Create views/app-versions.blade.php
2. Implement controller method with paginated versions
3. Add route for versions page
4. Reuse version item component from detail page

## Architecture

### Route
```
GET /app/{id}/versions -> getAppVersions($id)
```

### Page Sections
```
[Breadcrumb: Home > Apps > App Name > Versions]
[App Mini Header: icon, name, developer]
[Download Latest Button]
[Versions Title: "Old Versions of {App Name}"]
[Versions List: paginated, 10 per page]
[Load More / Pagination]
[Sidebar: APKPure widget, App Info, Similar Apps]
```

## Related Files

| File | Action | Notes |
|------|--------|-------|
| `views/app-versions.blade.php` | CREATE | Versions listing page |
| `partials/detail/version-item-full.blade.php` | CREATE | Full version item |
| `src/Http/Controllers/ApkpureController.php` | UPDATE | Add getAppVersions |
| `routes/web.php` | UPDATE | Add /app/{id}/versions route |

## Implementation Steps

### 1. Update Controller

```php
// ApkpureController.php

public function getAppVersions($id)
{
    $app = App::with(['developer', 'categories', 'lastedVersion'])
        ->findOrFail($id);

    $versions = AppVersion::where('app_id', $id)
        ->latest('release_date')
        ->paginate(10);

    // Similar apps for sidebar
    $categoryIds = $app->categories->pluck('id');
    $similarApps = App::with(['categories'])
        ->whereHas('categories', fn($q) => $q->whereIn('ac_app_categories.id', $categoryIds))
        ->where('id', '!=', $app->id)
        ->limit(3)
        ->get();

    // SEO
    Theme::setTitle($app->name . ' Old Versions - APKPure');
    Theme::meta('description', 'Download old versions of ' . $app->name . ' APK for Android.');

    return Theme::scope('app-versions', compact('app', 'versions', 'similarApps'))->render();
}
```

### 2. Update Routes

```php
// routes/web.php
Route::get('/app/{id}/versions', 'getAppVersions')->name('public.app.versions');
```

### 3. Create partials/detail/version-item-full.blade.php

```blade
@props(['app', 'version'])

<li class="ver-item-wrap">
  <a href="{{ $version->origin_download_url ?? '#' }}" class="ver-download-link">
    <div class="ver-item">
      <div class="ver-item-name">
        <span class="name">{{ $app->name }}</span>
        <span class="version">{{ $version->version }}</span>
        {{-- Add variant badge if multiple files per version --}}
      </div>
      <div class="ver-item-info">
        <span class="ver-size">{{ number_format($version->file_size / 1024 / 1024, 1) }} MB</span>
        <span class="ver-date">{{ $version->release_date?->format('M d, Y') }}</span>
      </div>
    </div>
    <div class="ver-download-btn">
      <span class="icon-download"></span>
      Download
    </div>
  </a>
</li>
```

### 4. Create views/app-versions.blade.php

```blade
@extends(Theme::getThemeNamespace('layouts.default'))

@push('styles')
<link rel="stylesheet" href="{{ Theme::asset()->url('css/app-detail.css') }}">
<link rel="stylesheet" href="{{ Theme::asset()->url('css/app-versions.css') }}">
@endpush

@section('breadcrumbs')
<div class="breadcrumb-wrap">
  <div class="breadcrumb">
    <a href="{{ route('public.index') }}">Home</a>
    <span class="separator">/</span>
    <a href="{{ route('public.apps') }}">Apps</a>
    <span class="separator">/</span>
    <a href="{{ route('public.app.detail', $app->id) }}">{{ $app->name }}</a>
    <span class="separator">/</span>
    <span class="current">Versions</span>
  </div>
</div>
@endsection

@section('content')
  {{-- App Mini Header --}}
  <div class="versions-app-header">
    <img class="versions-app-icon"
         src="{{ RvMedia::getImageUrl($app->logo, 'app-icon') }}"
         alt="{{ $app->name }}"
         width="64" height="64">
    <div class="versions-app-info">
      <h1 class="versions-app-title">{{ $app->name }}</h1>
      <p class="versions-app-developer">{{ $app->developer?->name }}</p>
    </div>
  </div>

  {{-- Download Latest --}}
  <div class="versions-download-latest">
    <a href="{{ $app->lastedVersion?->origin_download_url ?? route('public.app.detail', $app->id) }}"
       class="download-latest-btn">
      <span class="download-icon"></span>
      Download Latest Version
    </a>
  </div>

  {{-- Versions Content --}}
  <div class="versions-content">
    <h2 class="versions-title">Old Versions of {{ $app->name }}</h2>

    <ul class="versions-list-full">
      @forelse($versions as $version)
        @include(Theme::getThemeNamespace('partials.detail.version-item-full'), [
          'app' => $app,
          'version' => $version
        ])
      @empty
        <li class="ver-item-wrap">
          <p class="text-muted">No versions available.</p>
        </li>
      @endforelse
    </ul>

    {{-- Pagination / Load More --}}
    @if($versions->hasPages())
    <div class="load-more-wrap">
      {{ $versions->links() }}
    </div>
    @endif
  </div>
@endsection

@section('sidebar')
  @include(Theme::getThemeNamespace('partials.sidebar.apkpure-app-widget'))

  {{-- App Info Sidebar --}}
  <div class="sidebar-widget">
    <h3 class="widget-title">App Info</h3>
    <div class="app-info-sidebar">
      <div class="info-row">
        <span class="info-label">Category</span>
        <span class="info-value">{{ $app->categories->first()?->name ?? 'N/A' }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Requires</span>
        <span class="info-value">{{ $app->requires_android_os ?? 'Android 5.0+' }}</span>
      </div>
    </div>
  </div>

  @if($similarApps->count() > 0)
  <div class="sidebar-widget">
    <h3 class="widget-title">Similar Apps</h3>
    <div class="top-apps-list">
      @foreach($similarApps as $similar)
      <a href="{{ route('public.app.detail', $similar->id) }}" class="top-app-item">
        <img class="app-icon"
             src="{{ RvMedia::getImageUrl($similar->logo, 'app-icon') }}"
             alt="{{ $similar->name }}"
             width="44" height="44">
        <div class="app-info">
          <p class="app-name">{{ $similar->name }}</p>
          <p class="app-category">{{ $similar->categories->first()?->name }}</p>
        </div>
      </a>
      @endforeach
    </div>
  </div>
  @endif
@endsection
```

### 5. Add app-versions.css Styles (if not copied)

Ensure `public/css/app-versions.css` contains styles for:
- `.versions-app-header`
- `.versions-download-latest`
- `.versions-content`
- `.versions-list-full`
- `.ver-item-wrap`
- `.load-more-wrap`

## Todo List

- [ ] Add getAppVersions method to controller
- [ ] Register /app/{id}/versions route
- [ ] Create partials/detail/version-item-full.blade.php
- [ ] Create views/app-versions.blade.php
- [ ] Ensure app-versions.css is copied to public
- [ ] Test pagination
- [ ] Test download links

## Success Criteria

1. Versions page loads with app info
2. All versions listed with correct data
3. Pagination works correctly
4. Download links functional
5. "Download Latest" button works
6. Sidebar shows app info and similar apps

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Many versions slow page | Poor performance | Paginate to 10 per page |
| No versions for app | Empty page | Show "No versions" message |
| Invalid download URLs | Broken links | Validate or fallback to detail page |

## Next Steps

After completion, proceed to [Phase 7: Search & Categories](./phase-07-search.md)
