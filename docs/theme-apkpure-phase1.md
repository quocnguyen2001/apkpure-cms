# APKPure Theme - Phase 1 Implementation

**Version:** 1.0.0
**Status:** Completed
**Date:** 2026-01-27

---

## Overview

Phase 1 establishes the APKPure theme foundation for Botble CMS with Bootstrap 5.3.3 styling, essential layout pages (apps list, app detail, versions), and media size definitions.

---

## Theme Metadata

**ID:** `wallis/apkpure`
**Namespace:** `Theme\Apkpure\`
**Author:** Wallis
**Required Plugin:** `apkpure-crawler`

---

## Dependencies

### External Libraries (CDN)

| Library | Version | Purpose |
|---------|---------|---------|
| Bootstrap | 5.3.3 | Responsive grid & components |
| Bootstrap Icons | 1.11.3 | Icon set (bi bi-*) |
| jQuery | 3.7.1 | DOM manipulation & utilities |

**Note:** Bootstrap CSS loaded in `<head>`, jQuery in footer for performance.

---

## Configuration

### Asset Registration (config.php)

**CSS Assets:**
- `bootstrap-css` (5.3.3) - CDN via jsDelivr
- `bootstrap-icons` (1.11.3) - Icon font
- `main-style` - Theme styles (compiled SCSS)

**JS Assets:**
- `jquery` (3.7.1) - Footer container for optimal load time

**Shortcode Support:** Enabled via `shortcode()` function check in `beforeRenderTheme` hook.

---

## Media Sizes

Registered in `functions.php` app bootstrap callback:

| Size | Dimensions | Purpose |
|------|-----------|---------|
| `app-icon` | 72×72 | Small app icon thumbnails |
| `app-icon-large` | 120×120 | Medium app icon display |
| `screenshot` | 320×569 | Mobile screenshot preview |
| `banner` | 868×170 | Header/promotional banners |

**Usage in templates:**
```blade
<img src="{{ $app->image_url('app-icon') }}" alt="App icon" />
<img src="{{ $app->image_url('screenshot') }}" alt="Screenshot" />
```

---

## Theme Supports

Registered via `ThemeSupport` class:

- **Social Links** - Footer social media links
- **Toast Notifications** - User feedback messages
- **Preloader** - Page loading indicator
- **Site Copyright** - Footer copyright text
- **Date Format** - Customizable date display
- **Lazy Load Images** - Performance optimization
- **Social Sharing** - App sharing buttons
- **Site Logo Height** - Configurable header logo

---

## Frontend Pages

### Template Structure

| File | Purpose |
|------|---------|
| `index.html` | Homepage with featured apps |
| `apps.html` | Apps list/browsing page |
| `app-detail.html` | Single app detail view |
| `app-versions.html` | App version history |
| `games.html` | Games category page |

### Stylesheets

**SCSS Organization:**
```
assets/sass/
├── _variables.scss      # Theme variables & colors
├── main.scss           # Global styles
├── app-detail.scss     # App detail page styles
├── app-versions.scss   # Versions page styles
```

**Compiled CSS** (`public/css/`) via webpack.mix.js production builds.

---

## Sidebar Registration

**Primary Sidebar** (`primary_sidebar`):
- **Description:** Widgets for app detail pages
- **Location:** App detail template sidebar area
- **Common Widgets:** Related apps, recommendations, ad space

---

## Build Process

### Webpack Configuration (webpack.mix.js)

**SCSS Compilation:**
```javascript
.sass(source + '/assets/sass/style.scss', dist + '/css')
.sass(source + '/assets/sass/main.scss', dist + '/css')
.sass(source + '/assets/sass/app-detail.scss', dist + '/css')
.sass(source + '/assets/sass/app-versions.scss', dist + '/css')
.js(source + '/assets/js/script.js', dist + '/js')
```

**Production Build:**
- Compiles SCSS to CSS in `public/themes/apkpure/css/`
- Copies compiled CSS to `platform/themes/apkpure/public/css/`
- Minifies JavaScript output

**Command:**
```bash
npm run production  # Full build + optimization
npm run dev        # Development build with source maps
```

---

## Page Templates

### Apps List Page
- Responsive grid layout (col-12, col-sm-6, col-lg-4, col-xl-3)
- App cards with icon, name, rating, download count
- Category filtering & sorting
- Search functionality

### App Detail Page
- Full app information (name, description, ratings)
- Screenshots carousel
- Version history table (latest 5 versions)
- Related apps sidebar
- Permission list (from apkpure-crawler plugin)
- Download button(s) with version selection

### App Versions Page
- Complete version changelog
- Version table: name, code, release date, size
- Download links per version
- Release notes/change logs

---

## Color System

**Primary Colors:**
- Primary action buttons
- Links & hover states
- Focus indicators

**Extended Palette:**
- Info (blue) - Informational messages
- Success (green) - Positive feedback
- Warning (yellow) - Caution messages
- Danger (red) - Destructive actions

---

## Responsive Design

**Breakpoints:** Bootstrap 5 standard
- Extra small: < 576px (mobile)
- Small: ≥ 576px (small tablet)
- Medium: ≥ 768px (tablet)
- Large: ≥ 992px (desktop)
- Extra large: ≥ 1200px (large desktop)

**Grid:**
- 12-column system
- Default gutter: 24px
- Mobile-first approach

---

## Performance Considerations

1. **CDN Resources:** Bootstrap & icons via jsDelivr (fast, cached)
2. **Lazy Loading:** Enabled for media files
3. **Critical CSS:** Inline above-fold styles
4. **jQuery:** Deferred to footer (non-blocking)
5. **Asset Versioning:** Automatic cache busting via CMS version

---

## Next Steps (Phase 2+)

- [ ] Custom widget components for app recommendations
- [ ] Advanced filtering system (price, rating, size)
- [ ] User ratings & reviews system
- [ ] Download tracking & analytics
- [ ] Multi-language support (i18n)
- [ ] Dark mode variant
- [ ] Performance optimization (image optimization, code splitting)

---

## References

- [Bootstrap 5.3 Documentation](https://getbootstrap.com/docs/5.3)
- [Bootstrap Icons](https://icons.getbootstrap.com)
- [Botble CMS Theme Development](https://botble.com/docs)
- [Laravel Mix](https://laravel-mix.com)

