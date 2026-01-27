---
title: "Implement APKPure Theme"
description: "Complete theme implementation for APKPure CMS with app/game listings, detail pages, and version management"
status: in-progress
priority: P1
effort: 16h
progress: "25%"
completed-phases: 2
total-phases: 8
branch: main
tags: [theme, frontend, botble-cms, apkpure]
created: 2026-01-27
---

# APKPure Theme Implementation Plan

## Overview

Implement `platform/themes/apkpure` theme consuming data from `apkpure-crawler` plugin. Convert HTML templates from `frontend-templates/` to Blade views.

## Phases

| Phase | Description | Effort | Status |
|-------|-------------|--------|--------|
| 1 | [Foundation & Configuration](./phase-01-foundation.md) | 2h | done (2026-01-27) |
| 2 | [Layouts & Partials](./phase-02-layouts.md) | 2h | done (2026-01-27) |
| 3 | [Homepage Implementation](./phase-03-homepage.md) | 2.5h | pending |
| 4 | [Apps/Games Listing](./phase-04-listings.md) | 2h | pending |
| 5 | [App Detail Page](./phase-05-app-detail.md) | 2.5h | pending |
| 6 | [Versions Page](./phase-06-versions.md) | 1.5h | pending |
| 7 | [Search & Categories](./phase-07-search.md) | 1.5h | pending |
| 8 | [Widgets & Shortcodes](./phase-08-widgets.md) | 2h | pending |

## Data Models

- **App**: name, logo, images[], description, content, requires_android_os, developer_id, lasted_version_id
- **AppVersion**: version, release_date, file_size, changelog, app_id
- **AppCategory/AppTag**: name, slug (many-to-many with App)
- **Developer**: name, logo, description

## Key Dependencies

- `apkpure-crawler` plugin for models
- Bootstrap 5 CSS (CDN)
- Bootstrap Icons (CDN)
- Custom CSS from `frontend-templates/assets/css/`

## File Structure (Target)

```
platform/themes/apkpure/
├── theme.json (updated)
├── config.php (asset registration)
├── functions/
│   ├── functions.php (media sizes, sidebars)
│   ├── shortcodes.php (app listings)
│   └── theme-options.php
├── views/
│   ├── index.blade.php
│   ├── apps.blade.php
│   ├── games.blade.php
│   ├── app-detail.blade.php
│   ├── app-versions.blade.php
│   ├── category.blade.php
│   └── search.blade.php
├── layouts/default.blade.php
├── partials/
│   ├── header.blade.php
│   ├── footer.blade.php
│   ├── breadcrumbs.blade.php
│   ├── app-item.blade.php
│   ├── sidebar/
│   │   ├── apkpure-app-widget.blade.php
│   │   ├── top-downloads.blade.php
│   │   └── trending-games.blade.php
│   └── shortcodes/
│       ├── trending-apps.blade.php
│       ├── popular-games.blade.php
│       └── new-releases.blade.php
├── widgets/
│   └── top-downloads/
├── routes/web.php
├── src/Http/Controllers/ApkpureController.php
└── public/
    ├── css/ (main.css, app-detail.css, app-versions.css)
    └── js/script.js
```

## Success Criteria

1. Homepage renders with dynamic app data
2. Apps/Games listing with category filters working
3. App detail page shows all info, screenshots, versions
4. Versions page with load more pagination
5. Search returns relevant apps
6. Responsive design matches HTML templates
7. SEO meta tags properly set

## Validation Summary

**Validated:** 2026-01-27
**Questions asked:** 5

### Confirmed Decisions

| Decision | User Choice |
|----------|-------------|
| Download strategy | Direct file download - host APK files locally |
| Banner selection | Admin configurable via theme options |
| User reviews | Skip for now - show placeholder |
| App ratings | Display random placeholder ratings (4.0-4.8) |
| i18n support | Full i18n setup with translation keys |

### Action Items

- [ ] Add banner apps selection to theme-options.php
- [ ] Use local download URLs from AppVersion model
- [ ] Add random rating helper (4.0-4.8 range)
- [ ] Prepare lang/en.json with all translatable strings
- [ ] Add lang/vi.json for Vietnamese translations

## Sample Data

**Seeder**: `database/seeders/ApkpureSeeder.php` (already registered in DatabaseSeeder)

Run seeder:
```bash
php artisan db:seed --class=ApkpureSeeder
```

**Data Includes:**
- 8 developers (Google, Meta, Spotify, Supercell, SYBO, King, Telegram)
- 9 categories (Social, Entertainment, Music, Communication, Video, Games, Action, Arcade, Tools)
- 8 tags (Free, Popular, Trending, Editor Choice, New Release, Top Rated, Offline, Dark Mode)
- 11 apps (YouTube, Instagram, WhatsApp, Spotify, Maps, Telegram, TikTok)
- 5 games (Clash of Clans, Subway Surfers, Candy Crush, Clash Royale, Brawl Stars)

## Important Notes

**Games Filtering:** Games are determined by category, not platform. Filter apps with `categories` containing "Games":
```php
$games = App::whereHas('categories', fn($q) => $q->where('name', 'Games'))->get();
```

**Platform Enum:** `AppPlatformEnum` has ANDROID and IOS values only (not APP/GAME).

## Phase 1 Completion (2026-01-27)

**Completed Tasks:**
- theme.json updated with proper metadata
- CSS assets copied (main.css, app-detail.css, app-versions.css)
- config.php configured with Bootstrap CDN assets
- functions.php with media sizes and sidebar registration
- webpack.mix.js configured for SCSS compilation
- SCSS source files created (_variables.scss, app-detail.scss, app-versions.scss, main.scss)

**Status:** DONE - Foundation layer ready for Phase 2 layout work

## Phase 2 Completion (2026-01-27)

**Completed Tasks:**
- layouts/default.blade.php - Master layout with @yield sections for header, content, footer
- partials/header.blade.php - Navigation, search bar, user menu, app categories
- partials/footer.blade.php - 4-column footer with links and social media icons
- partials/breadcrumbs.blade.php - Dynamic breadcrumb navigation with schema markup
- partials/sidebar/apkpure-app-widget.blade.php - Featured app widget
- partials/sidebar/top-downloads.blade.php - Top 5 downloads sidebar
- partials/sidebar/trending-games.blade.php - Trending games widget
- routes/web.php - Route definitions for all major pages
- ApkpureController.php - Request handlers with CSRF/security fixes and proper error handling

**Status:** DONE - Layout infrastructure and route handlers ready for Phase 3 homepage implementation
