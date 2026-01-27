# Phase 1: Theme Foundation & Configuration

**Parent**: [plan.md](./plan.md)
**Priority**: P1 | **Status**: pending | **Effort**: 2h

## Overview

Configure theme metadata, asset registration, and media sizes. Establishes base infrastructure for all subsequent phases.

## Key Insights

- Existing scaffolding has basic structure but needs `apkpure-crawler` plugin dependency
- CSS from `frontend-templates/assets/css/` uses CSS variables (modern approach)
- Bootstrap 5 already registered via CDN; keep this approach
- Media sizes needed: app-icon (72x72), app-icon-large (120x120), screenshot, banner

## Requirements

1. Update theme.json with correct metadata and required_plugins
2. Configure config.php with proper asset registration
3. Set up functions.php with media sizes and ThemeSupport features
4. Copy CSS assets to theme public directory
5. Set up webpack.mix.js for future SCSS compilation

## Architecture

### theme.json
```json
{
  "id": "wallis/apkpure",
  "name": "APKPure",
  "namespace": "Theme\\Apkpure\\",
  "author": "Wallis",
  "version": "1.0.0",
  "description": "APK download theme for Botble CMS",
  "required_plugins": ["apkpure-crawler"]
}
```

### config.php Asset Registration
```php
// CSS
$theme->asset()->add('bootstrap-css', CDN_URL);
$theme->asset()->add('bootstrap-icons', CDN_URL);
$theme->asset()->usePath()->add('main-style', 'css/main.css');
$theme->asset()->usePath()->add('app-detail-style', 'css/app-detail.css');

// JS (footer)
$theme->asset()->container('footer')->add('jquery', CDN_URL);
$theme->asset()->container('footer')->usePath()->add('script', 'js/script.js');
```

### functions.php Media Sizes
```php
RvMedia::addSize('app-icon', 72, 72)
    ->addSize('app-icon-large', 120, 120)
    ->addSize('screenshot', 320, 569)
    ->addSize('banner', 868, 170);
```

## Related Files

| File | Action | Notes |
|------|--------|-------|
| `theme.json` | UPDATE | Add required_plugins, author |
| `config.php` | UPDATE | Asset registration |
| `functions/functions.php` | UPDATE | Media sizes, sidebars |
| `public/css/main.css` | CREATE | Copy from frontend-templates |
| `public/css/app-detail.css` | CREATE | Copy from frontend-templates |
| `public/css/app-versions.css` | CREATE | Copy from frontend-templates |
| `webpack.mix.js` | UPDATE | Configure SCSS paths |

## Implementation Steps

### 1. Update theme.json
- [ ] Set id to "wallis/apkpure"
- [ ] Set author to "Wallis"
- [ ] Add "apkpure-crawler" to required_plugins array
- [ ] Update description

### 2. Copy CSS Assets
- [ ] Copy `frontend-templates/assets/css/main.css` to `public/css/main.css`
- [ ] Copy `frontend-templates/assets/css/app-detail.css` to `public/css/app-detail.css`
- [ ] Copy `frontend-templates/assets/css/app-versions.css` to `public/css/app-versions.css`

### 3. Update config.php
- [ ] Keep Bootstrap 5 CDN registration
- [ ] Keep Bootstrap Icons CDN registration
- [ ] Add main.css with usePath()
- [ ] Register app-detail.css conditionally (view composer)
- [ ] Register app-versions.css conditionally
- [ ] Add jQuery CDN to footer container
- [ ] Add custom script.js to footer

### 4. Update functions.php
- [ ] Register media sizes for app icons (72x72, 120x120)
- [ ] Register media size for screenshots (320x569)
- [ ] Register media size for banners (868x170)
- [ ] Register primary_sidebar for app detail pages
- [ ] Keep existing ThemeSupport registrations

### 5. Update webpack.mix.js
- [ ] Set up SCSS compilation from assets/sass/
- [ ] Configure output to public/css/

## Todo List

- [ ] Update theme.json metadata
- [ ] Copy CSS files from frontend-templates
- [ ] Update config.php asset registration
- [ ] Update functions.php with media sizes
- [ ] Register sidebar for widgets
- [ ] Test theme activation in admin

## Success Criteria

1. Theme activates without errors
2. CSS loads correctly on frontend
3. apkpure-crawler plugin required on activation
4. Media sizes available in admin media library
5. Sidebar registered in admin widgets

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Plugin not installed | Theme fails to activate | Clear error message in required_plugins |
| CSS path issues | Broken styling | Test with browser dev tools |
| Bootstrap version conflict | Layout breaks | Pin to Bootstrap 5.3.x |

## Next Steps

After completion, proceed to [Phase 2: Layouts & Partials](./phase-02-layouts.md)
