# APKPure Theme - Assets & Build Reference

**Version:** 1.0.0
**Updated:** 2026-01-27

---

## Asset Loading Pipeline

### Request Flow

```
Browser Request
    ↓
Botble Theme Engine (config.php hooks)
    ↓
beforeRenderTheme hook executes
    ├─ CSS: Bootstrap 5.3.3 CDN
    ├─ CSS: Bootstrap Icons 1.11.3 CDN
    ├─ CSS: theme/main.css (compiled)
    └─ JS (footer): jQuery 3.7.1 CDN
    ↓
HTML rendered with assets
    ↓
Browser renders page
```

---

## CSS Assets

### External (CDN)

| Asset | URL | Version | Format | SRI |
|-------|-----|---------|--------|-----|
| Bootstrap | https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css | 5.3.3 | CSS | No |
| Icons | https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css | 1.11.3 | CSS | No |

**Load Location:** `<head>` (render-blocking)
**Provider:** jsDelivr (CDN, globally cached)

### Internal (Compiled SCSS)

| File | Source | Output | Purpose |
|------|--------|--------|---------|
| main.css | assets/sass/main.scss | public/css/main.css | Global styles |
| app-detail.css | assets/sass/app-detail.scss | public/css/app-detail.css | App detail page |
| app-versions.css | assets/sass/app-versions.scss | public/css/app-versions.scss | Versions page |

**Load Location:** `<head>` (after Bootstrap)
**Compilation:** webpack.mix.js → Laravel Mix

---

## JavaScript Assets

### External (CDN, Footer)

| Library | URL | Version | Purpose |
|---------|-----|---------|---------|
| jQuery | https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js | 3.7.1 | DOM manipulation |

**Load Location:** Footer (non-blocking)
**Loaded via:** `$theme->asset()->container('footer')->add(...)`

### Internal (Local)

| File | Source | Output | Purpose |
|------|--------|--------|---------|
| script.js | assets/js/script.js | public/js/script.js | Theme JS |

---

## Icon Systems

### Bootstrap Icons Usage

**Icon Font:** `bootstrap-icons@1.11.3`
**CSS Class:** `bi bi-{icon-name}`

**Common Icons:**
```html
<!-- Navigation -->
<i class="bi bi-house-door"></i>          <!-- Home -->
<i class="bi bi-grid"></i>                 <!-- Grid/List -->
<i class="bi bi-search"></i>               <!-- Search -->

<!-- App Actions -->
<i class="bi bi-download"></i>             <!-- Download -->
<i class="bi bi-star-fill"></i>            <!-- Rating -->
<i class="bi bi-share"></i>                <!-- Share -->

<!-- UI Controls -->
<i class="bi bi-chevron-right"></i>        <!-- Next/Arrow -->
<i class="bi bi-check-circle"></i>         <!-- Success -->
<i class="bi bi-exclamation-circle"></i>   <!-- Warning -->
<i class="bi bi-x-circle"></i>             <!-- Error -->
```

**Reference:** https://icons.getbootstrap.com

### Image Assets

**Location:** `frontend-templates/assets/images/`

| Asset | Usage | Dimensions |
|-------|-------|-----------|
| app-icon-*.svg | Mockup app icons (8 files) | Variable |
| category-*.svg | Category badges (6 files) | Variable |
| featured-banner.svg | Homepage banner | 868×170px |
| hero-phones.svg | Hero image mockup | Variable |
| logo.svg | Site logo | Variable |

---

## SCSS Organization

### File Hierarchy

```
assets/sass/
├── _variables.scss           # Color, spacing, typography variables
├── main.scss                 # Global styles (pages)
│   ├── Imports _variables
│   ├── General layout
│   ├── Header/nav styles
│   ├── Grid/card styles
│   └── Footer styles
├── app-detail.scss          # App detail page specific
│   ├── App header section
│   ├── Screenshots carousel
│   ├── Info panels
│   ├── Version table
│   └── Sidebar layout
└── app-versions.scss        # Version history page
    ├── Version timeline
    ├── Download links
    └── Change log format
```

### Compilation Output

```
Webpack.mix.js (Laravel Mix)
    ↓
Input: assets/sass/*.scss
    ↓
Output: public/css/
    ├── style.css
    ├── main.css
    ├── app-detail.css
    └── app-versions.css
```

---

## Build Commands

### Development Build

```bash
npm run dev
```

**Behavior:**
- Compiles SCSS with source maps
- JavaScript NOT minified
- Fast compilation
- Watches for file changes
- Suitable for local development

**Output:** `public/themes/apkpure/css/` and `public/themes/apkpure/js/`

### Production Build

```bash
npm run production
```

**Behavior:**
- Minifies all CSS & JS
- Removes source maps
- Optimizes for file size
- Copies compiled CSS to `platform/themes/apkpure/public/css/`
- Copies compiled JS to `platform/themes/apkpure/public/js/`

**Output Locations:**
- `public/themes/apkpure/css/` (dev build)
- `platform/themes/apkpure/public/css/` (production)

---

## Asset Versioning

### Cache Busting

**Method:** CMS version appended to asset URLs

```php
// config.php
version: $version  // Gets CMS version (e.g., 1.0.0)
```

**Result:**
```html
<link href="/themes/apkpure/css/main.css?id=1.0.0" rel="stylesheet">
```

**Benefit:** Changes invalidate browser cache automatically

---

## Media Sizes

### Registration

**File:** `functions.php`

```php
RvMedia::addSize('app-icon', 72, 72)
    ->addSize('app-icon-large', 120, 120)
    ->addSize('screenshot', 320, 569)
    ->addSize('banner', 868, 170);
```

### Usage in Templates

```blade
<!-- Single app by size -->
{{ $app->image_url('app-icon') }}        <!-- 72×72 -->
{{ $app->image_url('app-icon-large') }}  <!-- 120×120 -->
{{ $app->image_url('screenshot') }}      <!-- 320×569 -->
{{ $app->image_url('banner') }}          <!-- 868×170 -->

<!-- Rendered HTML -->
<img src="/storage/images/app-icon-72x72.jpg"
     alt="App Name"
     width="72"
     height="72" />
```

---

## Performance Metrics

### CSS Payload

| Source | Size | Cached |
|--------|------|--------|
| Bootstrap 5.3.3 | ~30KB | Yes (CDN) |
| Bootstrap Icons | ~40KB | Yes (CDN) |
| theme/main.css | ~5-8KB | Yes (versioned) |
| **Total** | **~75-78KB** | Mostly CDN |

### JS Payload

| Source | Size | Cached |
|--------|------|--------|
| jQuery 3.7.1 | ~82KB | Yes (CDN) |
| theme/script.js | ~2-3KB | Yes (versioned) |
| **Total** | **~84-85KB** | Mostly CDN |

### Optimization Tips

1. **CDN Caching:** Bootstrap & icons cached globally (jsDelivr, Cloudflare)
2. **Deferred JS:** jQuery loaded in footer (non-blocking)
3. **Media Optimization:** Use appropriate sizes from registration
4. **Lazy Loading:** Enable `loading="lazy"` on images below fold

---

## Browser Support

**Bootstrap 5.3.3** supports:
- Chrome (latest 2 versions)
- Edge (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- iOS Safari 12.2+
- Chrome for Android (latest)

**jQuery 3.7.1** supports:
- IE 9+ (deprecated)
- All modern browsers

---

## Customization Points

### Override Bootstrap Variables

**File:** `assets/sass/_variables.scss`

```scss
// Override before Bootstrap import
$primary: #your-color;
$secondary: #your-color;
$success: #your-color;
$danger: #your-color;
$warning: #your-color;
```

### Add Custom CSS

**File:** `assets/sass/main.scss`

```scss
// Add at end of file
.your-class {
    color: $primary;
    margin: 1rem;
}
```

### Extend jQuery

**File:** `assets/js/script.js`

```javascript
(function($) {
    $(document).ready(function() {
        // Your jQuery code here
    });
})(jQuery);
```

---

## Troubleshooting

### CSS Not Updating

**Issue:** Changes to SCSS not reflected in browser

**Solutions:**
1. Run `npm run production` for fresh build
2. Clear browser cache (Ctrl+Shift+Del)
3. Hard reload page (Ctrl+Shift+R)
4. Verify `public/themes/apkpure/css/` updated

### Icons Not Showing

**Issue:** Bootstrap icons missing in UI

**Solutions:**
1. Check DevTools Network tab for 404 errors
2. Verify CDN URL in `config.php` is correct
3. Use correct class format: `bi bi-icon-name`
4. Check for CSS class conflicts

### jQuery Not Available

**Issue:** `$ is not defined` in browser console

**Solutions:**
1. Verify jQuery loaded in footer (DevTools → Network)
2. Wrap code in jQuery ready: `$(document).ready(function() {...})`
3. Use `(function($) {...})(jQuery)` for noconflict mode
4. Check for JS errors before jQuery load

---

## CDN Provider Details

### jsDelivr (Bootstrap & Icons)

- **Provider:** jsDelivr (CDN)
- **Coverage:** Global (multiple PoPs)
- **Speed:** Optimized for fast delivery
- **SLA:** 99.9% uptime
- **Fallback:** No local fallback configured

### Cloudflare (jQuery)

- **Provider:** Cloudflare CDN (cdnjs.cloudflare.com)
- **Coverage:** Global
- **Speed:** Optimized routing
- **SLA:** Enterprise-grade reliability
- **Fallback:** No local fallback configured

---

## References

- [Laravel Mix Documentation](https://laravel-mix.com)
- [Webpack Configuration](https://webpack.js.org/configuration)
- [Bootstrap 5.3.3 Source](https://github.com/twbs/bootstrap/releases/tag/v5.3.3)
- [jsDelivr CDN](https://www.jsdelivr.com)

