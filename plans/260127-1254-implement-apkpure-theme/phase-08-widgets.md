# Phase 8: Widgets & Shortcodes

**Parent**: [plan.md](./plan.md) | **Depends on**: [Phase 7](./phase-07-search.md)
**Priority**: P2 | **Status**: pending | **Effort**: 2h

## Overview

Register theme widgets for admin sidebar management and shortcodes for dynamic content in CMS pages. Enables admin customization without code changes.

## Key Insights

- Botble widgets follow specific structure: registration.php + templates/frontend.blade.php
- Shortcodes registered in functions/shortcodes.php
- Shortcodes can have admin config forms for parameters
- Widgets placed in sidebars via admin UI
- Reference ripple theme for patterns

## Requirements

1. Register sidebar areas (primary_sidebar)
2. Create Top Downloads widget
3. Create Trending Apps widget
4. Create APKPure App widget
5. Register shortcodes for homepage sections
6. Create shortcode admin config forms

## Architecture

### Sidebar Areas
| Sidebar ID | Description | Used On |
|------------|-------------|---------|
| primary_sidebar | Right sidebar | All pages |
| footer_sidebar | Footer widgets | Optional |

### Widgets
| Widget | Purpose | Config Options |
|--------|---------|----------------|
| TopDownloadsWidget | Show top downloaded apps | limit, title |
| TrendingAppsWidget | Show trending apps | limit, title, platform |
| ApkpureAppWidget | APKPure app download CTA | download_url |

### Shortcodes
| Shortcode | Purpose | Parameters |
|-----------|---------|------------|
| [trending-apps] | Display trending apps | limit, title |
| [popular-games] | Display popular games | limit, title |
| [new-releases] | Display new releases | limit, title |
| [app-categories] | Display category grid | columns |

## Related Files

| File | Action | Notes |
|------|--------|-------|
| `functions/functions.php` | UPDATE | Register sidebars |
| `functions/shortcodes.php` | REWRITE | App shortcodes |
| `widgets/top-downloads/registration.php` | CREATE | Widget class |
| `widgets/top-downloads/templates/frontend.blade.php` | CREATE | Widget template |
| `widgets/trending-apps/registration.php` | CREATE | Widget class |
| `widgets/trending-apps/templates/frontend.blade.php` | CREATE | Widget template |
| `partials/shortcodes/trending-apps.blade.php` | CREATE | Shortcode template |
| `partials/shortcodes/popular-games.blade.php` | CREATE | Shortcode template |
| `partials/shortcodes/new-releases.blade.php` | CREATE | Shortcode template |

## Implementation Steps

### 1. Register Sidebars in functions.php

```php
// functions/functions.php

use Botble\Widget\Events\RenderingWidgetSettings;

app()->booted(function (): void {
    // ... existing code ...

    $events = app('events');

    $events->listen([RenderingWidgetSettings::class, 'core.widget:rendering'], function (): void {
        register_sidebar([
            'id' => 'primary_sidebar',
            'name' => __('Primary Sidebar'),
            'description' => __('Widgets for app pages sidebar'),
        ]);

        register_sidebar([
            'id' => 'footer_sidebar',
            'name' => __('Footer Sidebar'),
            'description' => __('Widgets for footer area'),
        ]);
    });
});
```

### 2. Create Top Downloads Widget

```php
// widgets/top-downloads/registration.php

use Botble\Widget\AbstractWidget;

class TopDownloadsWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Top Downloads'),
            'description' => __('Display top downloaded apps'),
            'limit' => 5,
            'title' => __('Top Downloads'),
        ]);
    }

    public function adminConfig(): string
    {
        return view('theme.apkpure::widgets.top-downloads.config', [
            'config' => $this->getConfig(),
        ])->render();
    }
}

register_widget(TopDownloadsWidget::class);
```

```blade
{{-- widgets/top-downloads/templates/frontend.blade.php --}}

@php
use Wallis\ApkpureCrawler\Models\App;

$limit = (int) ($config['limit'] ?? 5);
$title = $config['title'] ?? 'Top Downloads';
$apps = App::with(['categories'])->latest()->limit($limit)->get();
@endphp

@if($apps->count() > 0)
<div class="sidebar-widget">
  <h3 class="widget-title">{{ $title }}</h3>
  <div class="top-apps-list">
    @foreach($apps as $index => $app)
    <a href="{{ route('public.app.detail', $app->id) }}" class="top-app-item">
      <span class="rank">{{ $index + 1 }}</span>
      <img class="app-icon"
           src="{{ RvMedia::getImageUrl($app->logo, 'app-icon') }}"
           alt="{{ $app->name }}"
           width="44" height="44">
      <div class="app-info">
        <p class="app-name">{{ $app->name }}</p>
        <p class="app-category">{{ $app->categories->first()?->name }}</p>
      </div>
    </a>
    @endforeach
  </div>
</div>
@endif
```

### 3. Create Trending Apps Widget

```php
// widgets/trending-apps/registration.php

use Botble\Widget\AbstractWidget;

class TrendingAppsWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Trending Apps'),
            'description' => __('Display trending apps with trend indicator'),
            'limit' => 3,
            'title' => __('Trending Games'),
            'platform' => 'game', // app or game
        ]);
    }

    public function adminConfig(): string
    {
        return view('theme.apkpure::widgets.trending-apps.config', [
            'config' => $this->getConfig(),
        ])->render();
    }
}

register_widget(TrendingAppsWidget::class);
```

```blade
{{-- widgets/trending-apps/templates/frontend.blade.php --}}

@php
use Wallis\ApkpureCrawler\Models\App;
use Wallis\ApkpureCrawler\Enums\AppPlatformEnum;

$limit = (int) ($config['limit'] ?? 3);
$title = $config['title'] ?? 'Trending';
$type = $config['type'] ?? 'game'; // 'game' or 'app'

$query = App::with(['categories'])->latest();
if ($type === 'game') {
    // Filter by Games category
    $query->whereHas('categories', fn($q) => $q->where('name', 'Games'));
} elseif ($type === 'app') {
    // Exclude Games category
    $query->whereDoesntHave('categories', fn($q) => $q->where('name', 'Games'));
}
$apps = $query->limit($limit)->get();
@endphp

@if($apps->count() > 0)
<div class="sidebar-widget">
  <h3 class="widget-title">{{ $title }}</h3>
  <div class="top-apps-list">
    @foreach($apps as $app)
    <a href="{{ route('public.app.detail', $app->id) }}" class="top-app-item trending">
      <span class="rank trend-up"></span>
      <img class="app-icon"
           src="{{ RvMedia::getImageUrl($app->logo, 'app-icon') }}"
           alt="{{ $app->name }}"
           width="44" height="44">
      <div class="app-info">
        <p class="app-name">{{ $app->name }}</p>
        <p class="app-category">{{ $app->categories->first()?->name }}</p>
      </div>
    </a>
    @endforeach
  </div>
</div>
@endif
```

### 4. Register Shortcodes

```php
// functions/shortcodes.php

use Botble\Shortcode\Compilers\Shortcode as ShortcodeCompiler;
use Botble\Shortcode\Facades\Shortcode;
use Botble\Shortcode\Forms\ShortcodeForm;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Theme\Facades\Theme;
use Illuminate\Routing\Events\RouteMatched;
use Wallis\ApkpureCrawler\Models\App;
use Wallis\ApkpureCrawler\Enums\AppPlatformEnum;

app('events')->listen(RouteMatched::class, function (): void {

    if (!is_plugin_active('apkpure-crawler')) {
        return;
    }

    // Trending Apps Shortcode
    Shortcode::register(
        'trending-apps',
        __('Trending Apps'),
        __('Display trending apps grid'),
        function (ShortcodeCompiler $shortcode) {
            $limit = (int) ($shortcode->limit ?: 6);
            $title = $shortcode->title ?: __('Trending Now');

            $apps = App::with(['developer', 'categories'])
                ->where('platform', AppPlatformEnum::APP)
                ->latest()
                ->limit($limit)
                ->get();

            if ($apps->isEmpty()) {
                return null;
            }

            return Theme::partial('shortcodes.trending-apps', compact('apps', 'title', 'shortcode'));
        }
    );

    Shortcode::setAdminConfig('trending-apps', function (array $attributes) {
        return ShortcodeForm::createFromArray($attributes)
            ->add('title', TextField::class, TextFieldOption::make()->label(__('Title'))->defaultValue('Trending Now'))
            ->add('limit', NumberField::class, TextFieldOption::make()->label(__('Limit'))->defaultValue(6));
    });

    // Popular Games Shortcode
    Shortcode::register(
        'popular-games',
        __('Popular Games'),
        __('Display popular games grid'),
        function (ShortcodeCompiler $shortcode) {
            $limit = (int) ($shortcode->limit ?: 6);
            $title = $shortcode->title ?: __('Popular Games');

            $games = App::with(['developer', 'categories'])
                ->whereHas('categories', fn($q) => $q->where('name', 'Games'))
                ->latest()
                ->limit($limit)
                ->get();

            if ($games->isEmpty()) {
                return null;
            }

            return Theme::partial('shortcodes.popular-games', compact('games', 'title', 'shortcode'));
        }
    );

    Shortcode::setAdminConfig('popular-games', function (array $attributes) {
        return ShortcodeForm::createFromArray($attributes)
            ->add('title', TextField::class, TextFieldOption::make()->label(__('Title'))->defaultValue('Popular Games'))
            ->add('limit', NumberField::class, TextFieldOption::make()->label(__('Limit'))->defaultValue(6));
    });

    // New Releases Shortcode
    Shortcode::register(
        'new-releases',
        __('New Releases'),
        __('Display newly released apps'),
        function (ShortcodeCompiler $shortcode) {
            $limit = (int) ($shortcode->limit ?: 3);
            $title = $shortcode->title ?: __('New Releases');

            $apps = App::with(['developer', 'categories'])
                ->latest('created_at')
                ->limit($limit)
                ->get();

            if ($apps->isEmpty()) {
                return null;
            }

            return Theme::partial('shortcodes.new-releases', compact('apps', 'title', 'shortcode'));
        }
    );

    Shortcode::setAdminConfig('new-releases', function (array $attributes) {
        return ShortcodeForm::createFromArray($attributes)
            ->add('title', TextField::class, TextFieldOption::make()->label(__('Title'))->defaultValue('New Releases'))
            ->add('limit', NumberField::class, TextFieldOption::make()->label(__('Limit'))->defaultValue(3));
    });
});
```

### 5. Create Shortcode Templates

```blade
{{-- partials/shortcodes/trending-apps.blade.php --}}

<div class="category-apk-list-box category-module">
  <div class="category-module-title">
    <span class="title-text">{{ $title }}</span>
    <a href="{{ route('public.apps') }}" class="see-more">See All</a>
  </div>
  <div class="apk-list-column-three">
    @foreach($apps as $app)
      @include(Theme::getThemeNamespace('partials.app-item'), ['app' => $app])
    @endforeach
  </div>
</div>
```

```blade
{{-- partials/shortcodes/popular-games.blade.php --}}

<div class="category-apk-list-box category-module">
  <div class="category-module-title">
    <span class="title-text">{{ $title }}</span>
    <a href="{{ route('public.games') }}" class="see-more">See All</a>
  </div>
  <div class="apk-list-column-three">
    @foreach($games as $game)
      @include(Theme::getThemeNamespace('partials.app-item'), ['app' => $game])
    @endforeach
  </div>
</div>
```

```blade
{{-- partials/shortcodes/new-releases.blade.php --}}

<div class="category-apk-list-box category-module">
  <div class="category-module-title">
    <span class="title-text">{{ $title }}</span>
    <a href="{{ route('public.apps') }}" class="see-more">See All</a>
  </div>
  <div class="apk-list-column-three">
    @foreach($apps as $app)
      @include(Theme::getThemeNamespace('partials.app-item'), ['app' => $app])
    @endforeach
  </div>
</div>
```

## Todo List

- [ ] Update functions.php with sidebar registration
- [ ] Create widgets/top-downloads/ directory structure
- [ ] Create widgets/trending-apps/ directory structure
- [ ] Rewrite functions/shortcodes.php with app shortcodes
- [ ] Create partials/shortcodes/trending-apps.blade.php
- [ ] Create partials/shortcodes/popular-games.blade.php
- [ ] Create partials/shortcodes/new-releases.blade.php
- [ ] Test widgets in admin sidebar manager
- [ ] Test shortcodes in page editor
- [ ] Verify plugin active check works

## Success Criteria

1. Sidebars appear in admin widgets manager
2. Widgets can be added to sidebars
3. Widget config forms work in admin
4. Widgets render on frontend
5. Shortcodes available in page editor
6. Shortcode admin config forms work
7. Shortcodes render app data on pages

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Plugin not active | Shortcodes fail | Check is_plugin_active() |
| Widget registration errors | Admin breaks | Follow Botble widget structure exactly |
| Shortcode form fields wrong | Admin errors | Reference ripple theme patterns |
| N+1 query in widgets | Slow sidebar | Use eager loading |

## Post-Implementation Tasks

After all phases complete:
1. Test complete user journey (homepage -> listing -> detail -> download)
2. Cross-browser testing (Chrome, Firefox, Safari)
3. Responsive testing (mobile, tablet, desktop)
4. Performance audit (page load, assets)
5. SEO audit (meta tags, headings, structure)
6. Create theme documentation
