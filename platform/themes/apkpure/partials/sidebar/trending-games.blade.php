{{-- Trending Games Sidebar Widget --}}
@props(['games' => collect(), 'title' => __('Trending Games')])

<div class="sidebar-widget">
    <h3 class="widget-title">{{ $title }}</h3>
    <div class="top-apps-list">
        @forelse($games->take(5) as $game)
            <a href="{{ route('public.app.detail', $game->slug ?? $game->id) }}" class="top-app-item trending">
                <span class="rank trend-up"></span>
                <img class="app-icon" src="{{ RvMedia::getImageUrl($game->logo, 'app-icon', false, RvMedia::getDefaultImage()) }}" alt="{{ $game->name }}" width="44" height="44" loading="lazy">
                <div class="app-info">
                    <p class="app-name">{{ Str::limit($game->name, 20) }}</p>
                    <p class="app-category">{{ $game->categories->first()?->name ?? __('Game') }}</p>
                </div>
            </a>
        @empty
            <p class="no-apps">{{ __('No games available') }}</p>
        @endforelse
    </div>
</div>
