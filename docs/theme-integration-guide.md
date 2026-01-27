# APKPure Theme Integration Guide

**Updated:** 2026-01-27

---

## Quick Start

### 1. Install Theme & Plugin

```bash
# APKPure theme is in: platform/themes/apkpure/
# APKPure crawler plugin is required: plugin/apkpure-crawler/

# Activate theme in Botble Admin:
# Dashboard → Themes → APKPure → Activate
```

### 2. Build Assets

```bash
# Development build with source maps
npm run dev

# Production build (minified, optimized)
npm run production
```

### 3. Verify Installation

- [ ] Theme activated in admin panel
- [ ] Homepage loads without 404 errors
- [ ] CSS styling applied correctly
- [ ] App icons display properly
- [ ] Bootstrap 5.3.3 responsive layout works

---

## Asset Pipeline

### CSS Loading Order

1. **Bootstrap 5.3.3** (CDN) - Global reset & components
2. **Bootstrap Icons** (CDN) - Icon font
3. **theme/main.css** (local) - Theme overrides & custom styles
4. **page-specific CSS** (local) - app-detail.css, app-versions.css

### JS Loading

**Deferred to Footer:**
- jQuery 3.7.1 (CDN) - Non-blocking load

**Theme JS:**
- `/platform/themes/apkpure/public/js/script.js`

---

## Template Variables

### Global Variables (Available in All Views)

```blade
<!-- From Theme Support registration -->
{{ get_site_url() }}            <!-- Site URL -->
{{ setting_option('site_title') }} <!-- Site name -->
{{ setting_option('site_description') }}
{{ get_site_logo() }}           <!-- Logo URL -->
```

### App Variables

```blade
<!-- Single App -->
@foreach($apps as $app)
    $app->id
    $app->name
    $app->package_name
    $app->description
    $app->rating
    $app->download_count
    $app->icon_url
    $app->image_url('app-icon')        <!-- Registered size -->
    $app->image_url('screenshot')
    $app->image_url('banner')
    $app->category                     <!-- Belongs to category -->
    $app->versions()                   <!-- Version history -->
@endforeach
```

### Sidebar Variables

```blade
<!-- Primary Sidebar Widgets -->
<!-- Registered widgets auto-render in designated area -->
@if(is_active_sidebar('primary_sidebar'))
    <aside class="sidebar">
        {!! dynamic_sidebar('primary_sidebar') !!}
    </aside>
@endif
```

---

## Customization

### Modify Colors

**File:** `platform/themes/apkpure/assets/sass/_variables.scss`

```scss
// Override Bootstrap variables
$primary: #your-color;
$success: #your-color;
$warning: #your-color;
$danger: #your-color;
```

Recompile: `npm run production`

### Add Custom Page Template

1. Create blade file: `platform/themes/apkpure/views/your-template.blade.php`
2. Register in `functions.php`:
```php
register_page_template([
    'default' => __('Default'),
    'your-template' => __('Your Template Name'),
]);
```

### Register Custom Widget

1. Create widget class in plugin/apkpure-crawler/src/Widgets/
2. Register in plugin service provider
3. Available in admin Widget management UI

---

## Media Handling

### Displaying App Icons

```blade
<!-- Small icon (72×72) -->
<img src="{{ $app->image_url('app-icon') }}" alt="{{ $app->name }}" class="img-fluid" />

<!-- Large icon (120×120) -->
<img src="{{ $app->image_url('app-icon-large') }}" alt="{{ $app->name }}" />

<!-- Lazy load optimization -->
<img src="{{ $app->image_url('app-icon') }}"
     alt="{{ $app->name }}"
     loading="lazy"
     class="img-fluid" />
```

### Screenshots

```blade
<img src="{{ $version->image_url('screenshot') }}"
     alt="Screenshot"
     class="img-fluid rounded" />
```

### Banners

```blade
<img src="{{ $app->image_url('banner') }}"
     alt="Banner"
     class="w-100 rounded" />
```

---

## Common Modifications

### Add Google Analytics

**File:** `config.php` in `beforeRenderTheme` hook:

```php
'beforeRenderTheme' => function (Theme $theme): void {
    $theme->asset()->container('footer')->add(
        'google-analytics',
        'https://www.googletagmanager.com/gtag/js?id=YOUR_TRACKING_ID',
        [],
        [],
        defer: true
    );
    // Add inline script separately
},
```

### Add Custom Font

```php
'beforeRenderTheme' => function (Theme $theme): void {
    $theme->asset()->add(
        'custom-font',
        'https://fonts.googleapis.com/css2?family=YourFont:wght@400;600&display=swap'
    );
},
```

### Enable Shortcodes in Specific Pages

Already enabled for `page` layout. For custom post types:

```php
'beforeRenderLayout' => [
    'your-layout' => function (Theme $theme): void {
        if (function_exists('shortcode')) {
            $theme->composer(['your-layout'], function (View $view) {
                $view->withShortcodes();
            });
        }
    },
],
```

---

## Troubleshooting

### CSS Not Loading
- Run `npm run production` to compile SCSS
- Check theme is activated in admin
- Clear browser cache (Ctrl+Shift+Del)
- Check `platform/themes/apkpure/public/css/` exists

### Missing Icons
- Verify Bootstrap Icons CDN link active (no network block)
- Use `bi bi-icon-name` class (not `ti ti-*`)
- Reference: https://icons.getbootstrap.com

### jQuery Not Available
- Verify jQuery loaded in footer (browser DevTools → Network)
- Check for conflicts with other JS libraries
- Use `(function($) { ... })(jQuery)` pattern

### Images Not Displaying
- Verify app has media uploaded via apkpure-crawler
- Check media size registered: `RvMedia::addSize(...)`
- Use lazy loading attributes: `loading="lazy"`

---

## Performance Tips

1. **Image Optimization**
   - Compress images before upload
   - Use WebP format where possible
   - Lazy load below-fold images

2. **CSS/JS Optimization**
   - Minify in production (`npm run production`)
   - Remove unused Bootstrap utilities
   - Defer non-critical JS

3. **Caching**
   - Enable browser caching headers
   - Cache rendered theme assets
   - Use CDN for static files

4. **Database Queries**
   - Eager load relationships: `App::with('category', 'versions')`
   - Use pagination for large lists
   - Cache category listings

---

## File Structure Reference

```
platform/themes/apkpure/
├── theme.json                    # Theme metadata
├── config.php                    # Asset & feature config
├── functions.php                 # Media sizes, sidebars, supports
├── webpack.mix.js               # Build configuration
├── views/
│   ├── index.blade.php         # Homepage
│   ├── apps.blade.php          # Apps list
│   ├── app-detail.blade.php    # Single app
│   ├── app-versions.blade.php  # Version history
│   └── layouts/
│       └── base.blade.php      # Base layout
├── assets/
│   └── sass/                    # SCSS source
│       ├── _variables.scss
│       ├── main.scss
│       ├── app-detail.scss
│       └── app-versions.scss
└── public/
    └── css/                     # Compiled CSS (production)
```

---

## Support & Documentation

- **Botble CMS Docs:** https://botble.com/docs
- **Bootstrap 5.3:** https://getbootstrap.com/docs/5.3
- **Theme Issues:** Check `./logs` for error messages

