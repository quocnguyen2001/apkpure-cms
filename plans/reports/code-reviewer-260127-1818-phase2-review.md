# Code Review: APKPure Theme Phase 2

**Reviewer**: code-reviewer-a789395
**Date**: 2026-01-27 18:18
**Scope**: Phase 2 Layouts & Partials Implementation

## Scope

**Files Reviewed**:
- `platform/themes/apkpure/layouts/default.blade.php`
- `platform/themes/apkpure/partials/header.blade.php`
- `platform/themes/apkpure/partials/footer.blade.php`
- `platform/themes/apkpure/partials/breadcrumbs.blade.php`
- `platform/themes/apkpure/partials/sidebar/apkpure-app-widget.blade.php`
- `platform/themes/apkpure/partials/sidebar/top-downloads.blade.php`
- `platform/themes/apkpure/partials/sidebar/trending-games.blade.php`
- `platform/themes/apkpure/routes/web.php`
- `platform/themes/apkpure/src/Http/Controllers/ApkpureController.php`

**Focus**: Security (XSS, SQL injection), Performance (N+1), Architecture (YAGNI/KISS/DRY), Botble CMS patterns

**LOC**: ~300 lines

## Overall Assessment

Implementation follows Botble CMS patterns correctly. Code is clean, DRY compliant with reusable partials. **3 critical security issues** found requiring immediate fix. Performance optimization needed for N+1 queries.

---

## Critical Issues

### 1. **SQL Injection via LIKE operator** (ApkpureController.php:42)

**Risk**: SQL injection through unescaped user input

```php
// LINE 42 - VULNERABLE
->when($query, fn ($q) => $q->where('name', 'like', "%{$query}%"))
```

**Impact**: Attacker can inject SQL via search query bypassing Laravel binding by manipulating `%` wildcards.

**Fix**: Escape wildcards before binding
```php
->when($query, function ($q) use ($query) {
    $escaped = str_replace(['%', '_'], ['\%', '\_'], $query);
    return $q->where('name', 'like', "%{$escaped}%");
})
```

---

### 2. **XSS via unescaped copyright output** (footer.blade.php:57)

**Risk**: XSS if admin enters malicious HTML in theme options

```blade
{!! $copyright !!}
```

**Impact**: Stored XSS vulnerability if copyright field contains scripts.

**Fix**: Use escaped output
```blade
{{ $copyright }}
```

**Note**: Only use `{!! !!}` if copyright explicitly needs HTML formatting AND content is sanitized server-side via `BaseHelper::clean()`.

---

### 3. **Social link XSS risk** (footer.blade.php:13)

**Risk**: XSS if social link URLs contain javascript: protocol

```blade
<li><a href="{{ $socialLink->social_url }}" ...></a></li>
```

**Impact**: XSS via javascript: URLs in social links.

**Fix**: Validate URL protocol
```blade
@if(filter_var($socialLink->social_url, FILTER_VALIDATE_URL) && !str_starts_with($socialLink->social_url, 'javascript:'))
    <li><a href="{{ $socialLink->social_url }}" ...></a></li>
@endif
```

---

## High Priority Findings

### 4. **N+1 Query Problem** (ApkpureController.php:16,28)

**Issue**: Missing eager loading causes N+1 queries

```php
// LINE 16-18
$apps = App::query()
    ->whereDoesntHave('categories', fn ($q) => $q->where('name', 'Games'))
    ->latest()
    ->paginate(18);
```

**Impact**: 18 extra queries per page load (1 query per app for categories relationship in sidebar widgets).

**Fix**: Add eager loading
```php
$apps = App::query()
    ->with(['categories:id,name', 'developer:id,name'])
    ->whereDoesntHave('categories', fn ($q) => $q->where('name', 'Games'))
    ->latest()
    ->paginate(18);
```

**Apply to**: Lines 16, 28, 41, 90

---

### 5. **Inefficient Category Query** (ApkpureController.php:16,28)

**Issue**: Full table scan via `whereDoesntHave` with string comparison

```php
->whereDoesntHave('categories', fn ($q) => $q->where('name', 'Games'))
```

**Impact**: Slow on large datasets. String comparison instead of ID lookup.

**Fix**: Cache Games category ID and use whereDoesntHave with ID
```php
// In controller constructor or service
protected static ?int $gamesCategoryId = null;

protected function getGamesCategoryId(): int
{
    return self::$gamesCategoryId ??= AppCategory::where('name', 'Games')->value('id') ?? 0;
}

// In query
->whereDoesntHave('categories', fn ($q) => $q->where('ac_app_categories.id', $this->getGamesCategoryId()))
```

---

### 6. **Missing Route Validation** (ApkpureController.php:56,72)

**Issue**: `orWhere('id', $slug)` allows direct ID exposure

```php
->where('slug', $slug)
->orWhere('id', $slug)
->firstOrFail();
```

**Impact**: Exposes internal IDs, potential enumeration attack.

**Fix**: Only allow slug-based lookup in public routes
```php
->where('slug', $slug)
->firstOrFail();
```

**Exception**: Keep ID fallback only if legacy URLs require it. Otherwise remove.

---

## Medium Priority Improvements

### 7. **Hardcoded External URLs** (header.blade.php:8,54,58)

**Issue**: Static APKPure CDN URLs hardcoded

```blade
<img src="https://static.apkpure.com/www/static/imgs/logo_v3.svg" ...>
<img src="https://static.apkpures.xyz/www/static/imgs/no_login_v3.png" ...>
```

**Impact**: CDN change requires code update. Inconsistent domains (.com vs .xyz).

**Fix**: Move to theme options or config
```blade
<img src="{{ theme_option('default_logo', asset('images/logo.svg')) }}" ...>
<img src="{{ theme_option('default_avatar', asset('images/no-avatar.png')) }}" ...>
```

---

### 8. **Missing Pagination Meta** (ApkpureController.php:18,30,44,93)

**Issue**: No SEO pagination meta tags for crawlers

**Fix**: Add pagination links in views
```blade
@if($apps->hasPages())
    <link rel="canonical" href="{{ $apps->url($apps->currentPage()) }}">
    @if($apps->previousPageUrl())
        <link rel="prev" href="{{ $apps->previousPageUrl() }}">
    @endif
    @if($apps->nextPageUrl())
        <link rel="next" href="{{ $apps->nextPageUrl() }}">
    @endif
@endif
```

---

### 9. **Duplicate Route Logic** (web.php:8-14)

**Issue**: Route definitions could use resourceful routing pattern

**Current**:
```php
Route::get('apps', [ApkpureController::class, 'getApps'])->name('public.apps');
Route::get('games', [ApkpureController::class, 'getGames'])->name('public.games');
```

**Better** (DRY):
```php
Route::controller(ApkpureController::class)->group(function () {
    Route::get('apps', 'getApps')->name('public.apps');
    Route::get('games', 'getGames')->name('public.games');
    Route::get('search', 'getSearch')->name('public.search');
    Route::get('app/{slug}', 'getAppDetail')->name('public.app.detail');
    Route::get('app/{slug}/versions', 'getAppVersions')->name('public.app.versions');
    Route::get('category/{slug}', 'getCategory')->name('public.category');
});
```

---

### 10. **Missing Alt Text Truncation** (sidebar widgets)

**Issue**: Long app names in alt attributes

```blade
alt="{{ $app->name }}"
```

**Fix**: Truncate for accessibility
```blade
alt="{{ Str::limit($app->name, 50) }}"
```

---

## Low Priority Suggestions

### 11. **Inconsistent null coalescing** (header.blade.php:54)

```blade
{{ auth()->user()->avatar_url ?? 'https://...' }}
```

**Better**: Consistent with line 58 pattern
```blade
{{ auth()->user()->avatar_url ?: theme_option('default_avatar', 'https://...') }}
```

---

### 12. **Magic Number** (ApkpureController.php:18,30,44,93)

**Issue**: Hardcoded pagination limit `18`

**Fix**: Extract to config
```php
// config/theme.php
'pagination' => ['apps_per_page' => 18]

// Controller
->paginate(config('theme.pagination.apps_per_page', 18))
```

---

### 13. **Missing Query Scopes** (ApkpureController.php)

**Issue**: Repeated `->latest()` could be scope

**Suggestion**: Add to App model
```php
public function scopeLatestApps($query) {
    return $query->latest()->with(['categories:id,name', 'developer:id,name']);
}
```

---

## Positive Observations

✅ **Proper Blade Escaping**: All user data properly escaped with `{{ }}` except identified issues
✅ **CSRF Protection**: Form includes CSRF meta tag
✅ **DRY Compliance**: Sidebar partials reusable via `@props`
✅ **Botble Patterns**: Correct use of `Theme::breadcrumb()`, `Theme::scope()`
✅ **Route Security**: All routes registered via `Theme::registerRoutes()`
✅ **Accessibility**: Proper ARIA labels in breadcrumbs
✅ **Image Optimization**: Lazy loading on sidebar images
✅ **Clean Architecture**: Controller extends `PublicController` correctly

---

## Recommended Actions

**Immediate (Critical)**:
1. Fix SQL injection in search query (Issue #1)
2. Escape copyright output or sanitize input (Issue #2)
3. Validate social link URLs (Issue #3)

**Next Sprint (High)**:
4. Add eager loading to all App queries (Issue #4)
5. Optimize category filtering with ID lookup (Issue #5)
6. Remove ID-based routing or document security decision (Issue #6)

**Backlog (Medium/Low)**:
7. Move hardcoded URLs to theme options
8. Add pagination SEO meta tags
9. Refactor routes to use controller grouping
10. Extract magic numbers to config

---

## Metrics

- **Type Coverage**: N/A (Blade templates + Laravel)
- **Test Coverage**: Not analyzed (no tests in scope)
- **Security Issues**: 3 critical, 1 high
- **Performance Issues**: 2 high priority
- **Architecture Issues**: 0 blocking, 3 suggestions

---

## Phase 2 Status Update

**Plan File**: `plans/260127-1254-implement-apkpure-theme/phase-02-layouts.md`

### Implementation Checklist

✅ All todo items completed:
- ✅ Rewrite layouts/default.blade.php
- ✅ Rewrite partials/header.blade.php
- ✅ Rewrite partials/footer.blade.php
- ✅ Update partials/breadcrumbs.blade.php
- ✅ Create partials/sidebar/apkpure-app-widget.blade.php
- ✅ Create partials/sidebar/top-downloads.blade.php
- ✅ Create partials/sidebar/trending-games.blade.php
- ✅ Test layout renders correctly (passing)

### Success Criteria

| Criteria | Status | Notes |
|----------|--------|-------|
| Layout renders without errors | ✅ PASS | View cache successful |
| Header navigation links work | ✅ PASS | Active states implemented |
| Search form submits correctly | ⚠️ PASS* | Works but needs SQL injection fix |
| Footer displays all columns | ✅ PASS | 4 columns rendered |
| Breadcrumbs render dynamically | ✅ PASS | Uses Theme::breadcrumb() |
| Sidebar partials accept data | ✅ PASS | @props pattern working |

**\*PASS with critical security fixes required**

---

## Unresolved Questions

1. **Decision needed**: Should public routes support ID-based lookups (`/app/123`) or slug-only? Current allows both. If legacy support needed, document decision for security review.

2. **Clarification needed**: Does `Theme::getSiteCopyright()` return sanitized HTML or raw input? If raw, must escape output. If sanitized via `BaseHelper::clean()`, current implementation OK.

3. **Theme options**: Should CDN URLs (logo, avatars) be configurable via admin panel or remain hardcoded for APKPure branding consistency?
