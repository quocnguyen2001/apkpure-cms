# Botble CMS Theme Architecture Research
**Date:** 2026-01-27 | **Source:** ripple theme analysis

## 1. Directory Structure

Botble themes follow standardized directory layout:
```
ripple/
├── theme.json           # Theme metadata & configuration
├── config.php           # Theme event hooks & asset setup
├── composer.json        # Package dependencies (if exists)
├── routes/
│   └── web.php         # Theme-specific route registration
├── src/
│   └── Http/Controllers/RippleController.php
├── views/              # Blade templates for pages
│   ├── index.blade.php
│   ├── post.blade.php
│   ├── page.blade.php
│   ├── category.blade.php
│   └── ... (auth, search, error pages)
├── layouts/            # Master layout templates
│   ├── default.blade.php
│   └── no-sidebar.blade.php
├── partials/           # Reusable view components
│   ├── header.blade.php
│   ├── footer.blade.php
│   ├── breadcrumbs.blade.php
│   └── shortcodes/    # Shortcode partials
├── functions/          # Theme functionality & hooks
│   ├── functions.php   # Main theme setup (CRITICAL)
│   ├── shortcodes.php
│   ├── theme-options.php
│   └── ... (integration files)
├── widgets/            # Widget definitions
│   └── [widget-name]/
│       ├── registration.php
│       └── templates/
├── assets/             # Source assets
│   ├── scss/          # SCSS source files
│   └── js/
├── public/             # Compiled/distributed assets
│   ├── css/
│   ├── js/
│   ├── fonts/
│   └── images/
├── lang/               # i18n translations (multiple .json files)
├── webpack.mix.js      # Asset compilation config
└── screenshot.png      # Theme preview image
```

## 2. Configuration Files

### theme.json
- Required metadata: `id`, `name`, `namespace`, `description`, `author`, `version`
- Defines `required_plugins` array (enforces dependencies)
- Example: ripple requires blog, contact, gallery, language, member plugins

### config.php (CRITICAL)
Returns array with:
- `inherit`: Parent theme (for extending)
- `events` object with hooks:
  - `before`: Initial setup
  - `beforeRenderTheme`: Register assets (CSS/JS), setup composers
  - `beforeRenderLayout`: Layout-specific setup

Asset registration uses facade: `$theme->asset()->container('footer')->usePath()->add(...)`

### functions.php (CRITICAL - BOOT LOGIC)
Executes in `app()->booted()` callback. Registers:
- Media sizes: `RvMedia::addSize('featured', 565, 375)`
- Typography: fonts, font sizes via `Theme::typography()`
- Theme support features: social links, preloader, copyrightetc via `ThemeSupport::`
- Page templates: `register_page_template()`
- Sidebars/widgets: `register_sidebar()`
- Form extensions: customize content editing forms
- Event listeners for cross-plugin integration

## 3. Views & Blade Templating

### Layout Structure
- `layouts/default.blade.php`: Master layout wrapping all pages
- `layouts/no-sidebar.blade.php`: Alternative layout variant
- `@php Theme::layout('no-sidebar'); @endphp` directive selects layout per page

### View Files
Pages (posts, pages, categories, galleries) extend layouts. Path convention:
- `views/post.blade.php` → renders single blog post
- `views/page.blade.php` → renders CMS page
- `views/category.blade.php` → renders taxonomy

### Partials
- `partials/header.blade.php`, `partials/footer.blade.php`
- `partials/breadcrumbs.blade.php`, `partials/main-menu.blade.php`
- `partials/shortcodes/`: Blade templates for shortcode output

## 4. Theme Functions & Hooks

### Media/Typography System
- `RvMedia::addSize(name, width, height)`: Define image variants
- `Theme::typography()->registerFontFamilies()` / `registerFontSizes()`

### Features Registration
`ThemeSupport::` static methods register pre-built features:
- `registerSocialLinks()`, `registerSocialSharing()`
- `registerPreloader()`, `registerToastNotification()`
- `registerLazyLoadImages()`, `registerSiteCopyright()`

### Forms & Models
- Hook into `FormAbstract` to extend post/page editing forms
- Add custom fields with `$form->addAfter()`, validate on save with `FormAbstract::afterSaving()`

### Event Listeners
- Theme listens to plugin events: `core.page::registering-templates`, `RenderingWidgetSettings::class`
- Cross-plugin integration via event listeners

## 5. Routes & Controllers

### Route Registration
```php
Theme::registerRoutes(function (): void {
    Route::group(['controller' => RippleController::class], function (): void {
        Route::middleware(RequiresJsonRequestMiddleware::class)->group(function (): void {
            Route::get('ajax/search', 'getSearch')->name('public.ajax.search');
        });
    });
});
Theme::routes(); // Load plugin routes
```

- Custom theme routes in `routes/web.php`
- Controller: `src/Http/Controllers/RippleController.php`

## 6. Asset Management

### Compilation
- `webpack.mix.js`: Defines build process
- Source: `assets/scss/`, `assets/js/`
- Output: `public/css/`, `public/js/`

### Asset Registration (in config.php)
```php
$theme->asset()->container('footer')->usePath()->add('jquery', 'plugins/jquery/jquery-4.0.0.min.js');
$theme->asset()->usePath()->add('style', 'css/style.css', [], [], $version);
```
- `usePath()`: Assets in `public/` directory
- `container()`: Render location (header/footer)
- Versioning via `get_cms_version()`

### Asset Organization
- Bootstrap CSS (RTL support detected)
- jQuery, custom JS plugins
- Font files in `public/fonts/`
- UI block images for editor previews in `public/images/ui-blocks/`

## 7. Widgets & Shortcodes

### Widget Structure
```
widgets/[name]/
├── registration.php      # Widget class definition
└── templates/
    └── frontend.blade.php
```

### Shortcodes
- Defined in `functions/shortcodes.php`
- Blade partials in `partials/shortcodes/`
- Examples: `[featured-posts]`, `[recent-posts]`, `[all-galleries]`

## Key Takeaways for APKPure Theme

1. **Namespace-based**: Use `Theme\\Apkpure\\` namespace, register in `theme.json`
2. **Event-driven**: All initialization via `config.php` event hooks
3. **Modular partials**: Break UI into reusable blade components
4. **Asset versioning**: Integrate build pipeline with Webpack Mix
5. **Plugin integration**: Hook into core events for customization
6. **Media management**: Define image sizes for app icons, screenshots, banners
7. **Sidebars/widgets**: Register at boot time for admin UI
8. **Custom fields**: Extend forms for app-specific metadata

## Unresolved Questions
- Shortcode rendering mechanism (implementation details)
- Admin theme customizer integration flow
- Caching strategy for theme assets
