# APKPure Crawler Plugin Research Report

**Date:** 2026-01-27 | **Report:** researcher-02-apkpure-plugin.md

## Executive Summary

The apkpure-crawler plugin provides complete app data management infrastructure (Apps, Versions, Categories, Developers, Tags) with REST API, admin CRUD interfaces, and scrapers. Data is stored in separate tables with relationships. Plugin exposes models through Laravel ORM for theme consumption.

## Data Models & Database

### Core Tables
- **ac_apps** (Apps) - Main app record with logo, images, description, content, Android requirements, developer_id, latest_version_id
- **ac_app_versions** - Version-specific data (version_name, release_date, file_size, etc.) with foreign key to ac_apps
- **ac_developers** - Developer/publisher info (name, logo, description)
- **ac_app_categories** - Pivot table (ac_app_category) linking apps ↔ categories
- **ac_app_tags** - Pivot table (ac_app_tag) linking apps ↔ tags
- **ac_apps_translations** - i18n support (lang_code, description, content)

### Key Fields
- App: name, logo, images (JSON array), description, content (long text), requires_android_os, lasted_update (date), platform (enum), google_play URL, developer_id FK
- AppVersion: version_name, release_date, file_size, download_count, changelog
- Developer: name, logo, description, email

### Model Relationships (Eloquent)
```
App → belongsTo Developer
App → hasMany AppVersion
App → belongsTo AppVersion (lastedVersion)
App → belongsToMany AppCategory (pivot: ac_app_category)
App → belongsToMany AppTag (pivot: ac_app_tag)
```

## Routes & Controllers

### Admin Routes (Web)
- `apkpure-crawler/apps` - CRUD for apps (AppController)
- `apkpure-crawler/app-categories` - Category management (AppCategoryController)
- `apkpure-crawler/app-tags` - Tag management (AppTagController)
- `apkpure-crawler/developers` - Developer management (DeveloperController)
- `apkpure-crawler/app-versions` - Version management (AppVersionController)
  - `list/{id}` - Versions by app_id

### API Routes
- `POST /api/scraper/` - Initiate scrape job (ScraperController::scrape)
- `GET /api/scraper/{job}` - Check scrape status
- `POST /api/scraper/{job}/extract` - Extract data from scraped content

## Frontend Integration

### Available Data
Themes can directly query Eloquent models via:
```php
App::with('developer', 'versions', 'categories', 'tags')->paginate()
AppVersion::where('app_id', $appId)->get()
AppCategory::with('apps')->get()
Developer::with('apps')->get()
```

### Missing Frontend Helpers
- No explicit shortcodes or template tags documented
- No blade components/views exposed for themes
- No public facade/helper functions for theme consumption
- Themes must directly use models or create custom helpers

## Controllers & Forms

- **AppController, AppVersionController, etc.** - Standard Laravel admin controllers
- **AppForm, AppVersionForm, etc.** - Form builders for admin UI
- **AppTable, AppVersionTable, etc.** - Admin table views

## Key Observations

1. **Plugin doesn't export theme helpers** - Themes must use Eloquent models directly
2. **No published views** - All UI is admin-only; themes start from scratch
3. **Scraper architecture separate** - Scraping handled by PlaywrightClient, parser factory (AppDetailParser, CategoryPageParser, etc.)
4. **Translation support** - ac_apps_translations table enables multilingual descriptions/content
5. **Media management** - Separate MediaService handles image downloads/uploads

## Unresolved Questions

- Are there any theme-specific facades or service providers?
- What format are app images stored (URLs vs uploaded files)?
- Are there any cache helpers for theme performance?
- What's the relationship between scrape_ref_id and scraper tables?
