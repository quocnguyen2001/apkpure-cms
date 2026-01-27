{{-- Top Downloads Sidebar Widget --}}
@props(['apps' => collect(), 'title' => __('Top Downloads')])

<div class="sidebar-widget">
    <h3 class="widget-title">{{ $title }}</h3>
    <div class="top-apps-list">
        @forelse($apps->take(5) as $index => $app)
            <a href="{{ route('public.app.detail', $app->slug ?? $app->id) }}" class="top-app-item">
                <span class="rank">{{ $index + 1 }}</span>
                <img class="app-icon" src="{{ RvMedia::getImageUrl($app->logo, 'app-icon', false, RvMedia::getDefaultImage()) }}" alt="{{ $app->name }}" width="44" height="44" loading="lazy">
                <div class="app-info">
                    <p class="app-name">{{ Str::limit($app->name, 20) }}</p>
                    <p class="app-category">{{ $app->categories->first()?->name ?? __('App') }}</p>
                </div>
            </a>
        @empty
            <p class="no-apps">{{ __('No apps available') }}</p>
        @endforelse
    </div>
</div>
