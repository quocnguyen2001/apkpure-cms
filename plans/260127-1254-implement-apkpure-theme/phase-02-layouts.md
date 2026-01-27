# Phase 2: Layouts & Partials

**Parent**: [plan.md](./plan.md) | **Depends on**: [Phase 1](./phase-01-foundation.md)
**Priority**: P1 | **Status**: ✓ DONE (2026-01-27) | **Effort**: 2h | **Review**: [phase2-review](../reports/code-reviewer-260127-1818-phase2-review.md)

## Overview

Create master layout and reusable partials (header, footer, breadcrumbs, sidebar widgets). Foundation for all page templates.

## Key Insights

- HTML templates share common header/footer across all pages
- Header has: logo, nav (Home/Games/Apps/Articles), search, user avatar
- Footer has: 4 columns (Follow Us, Service, Developers, Company) + copyright
- Sidebar widgets reused: APKPure App widget, Top Downloads, Trending Games
- CSS uses flexbox layout: `.main-body` with `.left` (content) and `.right` (sidebar)

## Requirements

1. Create default.blade.php master layout
2. Create header.blade.php with navigation and search
3. Create footer.blade.php with footer columns
4. Create breadcrumbs.blade.php component
5. Create sidebar partials for reuse

## Architecture

### Layout Structure
```html
<!DOCTYPE html>
<html>
<head>{!! Theme::header() !!}</head>
<body>
  @include('theme.apkpure::partials.header')
  @yield('breadcrumbs')
  <div class="main-body">
    <div class="left">@yield('content')</div>
    <div class="right">@yield('sidebar')</div>
  </div>
  @include('theme.apkpure::partials.footer')
  {!! Theme::footer() !!}
</body>
</html>
```

### Header Navigation Items
| Label | URL | Icon Class | Active Condition |
|-------|-----|------------|------------------|
| Home | / | icon_home | Route is homepage |
| Games | /games | icon_game | Route starts with /games |
| Apps | /apps | icon_app | Route starts with /apps |
| Articles | /articles | icon_article | Route starts with /articles |

## Related Files

| File | Action | Notes |
|------|--------|-------|
| `layouts/default.blade.php` | REWRITE | Full layout from HTML template |
| `partials/header.blade.php` | REWRITE | Navigation, search, user |
| `partials/footer.blade.php` | REWRITE | Footer columns, social links |
| `partials/breadcrumbs.blade.php` | UPDATE | Dynamic breadcrumb component |
| `partials/sidebar/apkpure-app-widget.blade.php` | CREATE | APKPure app download widget |
| `partials/sidebar/top-downloads.blade.php` | CREATE | Top downloads list |
| `partials/sidebar/trending-games.blade.php` | CREATE | Trending games list |

## Implementation Steps

### 1. Create layouts/default.blade.php
- [ ] Add HTML doctype and lang attribute
- [ ] Add meta viewport, charset
- [ ] Call {!! Theme::header() !!} in head
- [ ] Include header partial
- [ ] Add breadcrumbs yield section
- [ ] Create main-body with left/right columns
- [ ] Content in left, sidebar in right
- [ ] Include footer partial
- [ ] Call {!! Theme::footer() !!} before body close

### 2. Create partials/header.blade.php
- [ ] Header element with #header ID
- [ ] nav_container with flexbox layout
- [ ] Logo with link to homepage (use theme_option or hardcode)
- [ ] Navigation menu with Home/Games/Apps/Articles
- [ ] Add active class logic based on current route
- [ ] Search form with proper action URL
- [ ] User avatar (static for now, member integration later)

### 3. Create partials/footer.blade.php
- [ ] Footer element with class "footer"
- [ ] 4 columns: Follow Us (social), Service, Developers, Company
- [ ] Social links from theme_option if available
- [ ] Copyright with dynamic year
- [ ] Privacy/Terms links from theme_option
- [ ] Language selector (static EN for now)

### 4. Create partials/breadcrumbs.blade.php
- [ ] Accept $items array parameter
- [ ] Loop through items, link all except last
- [ ] Last item as span with class "current"
- [ ] Separator spans between items

### 5. Create Sidebar Partials

#### partials/sidebar/apkpure-app-widget.blade.php
- [ ] Green gradient background widget
- [ ] APKPure icon, title, description
- [ ] Download button (link to theme_option URL)

#### partials/sidebar/top-downloads.blade.php
- [ ] Accept $apps collection parameter
- [ ] Loop with rank number (1-5)
- [ ] App icon, name, category
- [ ] Link to app detail

#### partials/sidebar/trending-games.blade.php
- [ ] Accept $games collection parameter
- [ ] Trending up indicator
- [ ] App icon, name, category
- [ ] Link to app detail

## Blade Component Pattern

```blade
{{-- partials/sidebar/top-downloads.blade.php --}}
@props(['apps', 'title' => 'Top Downloads'])

<div class="sidebar-widget">
  <h3 class="widget-title">{{ $title }}</h3>
  <div class="top-apps-list">
    @foreach($apps as $index => $app)
      <a href="{{ route('public.app.detail', $app->slug ?? $app->id) }}" class="top-app-item">
        <span class="rank">{{ $index + 1 }}</span>
        <img class="app-icon" src="{{ RvMedia::getImageUrl($app->logo, 'app-icon') }}" alt="{{ $app->name }}">
        <div class="app-info">
          <p class="app-name">{{ $app->name }}</p>
          <p class="app-category">{{ $app->categories->first()?->name }}</p>
        </div>
      </a>
    @endforeach
  </div>
</div>
```

## Todo List

- [x] Rewrite layouts/default.blade.php
- [x] Rewrite partials/header.blade.php
- [x] Rewrite partials/footer.blade.php
- [x] Update partials/breadcrumbs.blade.php
- [x] Create partials/sidebar/apkpure-app-widget.blade.php
- [x] Create partials/sidebar/top-downloads.blade.php
- [x] Create partials/sidebar/trending-games.blade.php
- [x] Test layout renders correctly

## Code Review Results

**Date**: 2026-01-27 | **Reviewer**: code-reviewer-a789395 | **Report**: [phase2-review.md](../reports/code-reviewer-260127-1818-phase2-review.md)

### Critical Issues Requiring Immediate Fix

1. ⚠️ **SQL Injection** - Search query vulnerable to LIKE injection (ApkpureController:42)
2. ⚠️ **XSS Risk** - Unescaped copyright output in footer (footer.blade.php:57)
3. ⚠️ **XSS Risk** - Social links need URL validation (footer.blade.php:13)

### High Priority Performance Issues

4. **N+1 Queries** - Missing eager loading on App queries (4 occurrences)
5. **Inefficient Query** - Category filtering uses string comparison instead of ID

### Recommendation

**Phase 2 implementation complete but requires security fixes before production deployment.**

All todo items completed successfully. Layout architecture follows Botble patterns correctly. Fix 3 critical security issues before proceeding to Phase 3.

## Success Criteria

1. Layout renders without PHP errors
2. Header navigation links work
3. Search form submits to correct route
4. Footer displays with all columns
5. Breadcrumbs render dynamically
6. Sidebar partials accept data and render

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Theme::header/footer issues | Broken page | Verify Botble facade imports |
| Missing routes | 404 errors | Use route() helper with fallback |
| RvMedia not available | Broken images | Check plugin active before calling |

## Next Steps

After completion, proceed to [Phase 3: Homepage](./phase-03-homepage.md)
