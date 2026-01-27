<header id="header">
    <div class="nav_container">
        {{-- Logo --}}
        <a class="header-logo-wrap" title="{{ theme_option('site_title', 'APKPure') }}" href="{{ BaseHelper::getHomepageUrl() }}">
            @if($logo = Theme::getLogo())
                {{ Theme::getLogoImage(maxHeight: 32, attributes: ['class' => 'logo-img']) }}
            @else
                <img src="https://static.apkpure.com/www/static/imgs/logo_v3.svg" alt="{{ theme_option('site_title', 'APKPure') }}" class="logo-img" height="32">
            @endif
        </a>

        {{-- Navigation --}}
        <div class="nav_new">
            <div class="item nav_home">
                <a href="{{ BaseHelper::getHomepageUrl() }}" class="dt_nav_button {{ request()->is('/') ? 'active' : '' }}" title="{{ __('Home') }}">
                    <i class="icon icon_home"></i>
                    <span class="dt_menu_text">{{ __('Home') }}</span>
                </a>
            </div>
            <div class="item">
                <a title="{{ __('Games') }}" class="dt_nav_button nav-g {{ request()->is('games*') ? 'active' : '' }}" href="{{ route('public.games') }}">
                    <i class="icon icon_game"></i>
                    <span class="dt_menu_text">{{ __('Games') }}</span>
                </a>
            </div>
            <div class="item">
                <a title="{{ __('Apps') }}" class="dt_nav_button nav-a {{ request()->is('apps*') ? 'active' : '' }}" href="{{ route('public.apps') }}">
                    <i class="icon icon_app"></i>
                    <span class="dt_menu_text">{{ __('Apps') }}</span>
                </a>
            </div>
            <div class="item">
                <a title="{{ __('Articles') }}" class="dt_nav_button {{ request()->is('articles*') || request()->is('blog*') ? 'active' : '' }}" href="{{ url('/blog') }}">
                    <i class="icon icon_article"></i>
                    <span class="dt_menu_text">{{ __('Articles') }}</span>
                </a>
            </div>
        </div>

        {{-- Search --}}
        <div class="item search">
            <form class="formsearch" method="get" action="{{ route('public.search') }}">
                <div class="search-input">
                    <input type="text" id="form_query" name="q" placeholder="{{ __('Search for Apps and Games') }}" autocomplete="off" value="{{ request()->query('q') }}">
                    <input class="search-btn-icon" type="submit" value="">
                </div>
            </form>
        </div>

        {{-- User --}}
        <div class="nav_user">
            @auth
                <a href="{{ route('public.member.dashboard') }}" title="{{ auth()->user()->name }}">
                    <img class="nav_user_img" alt="{{ auth()->user()->name }}" src="{{ auth()->user()->avatar_url ?? 'https://static.apkpures.xyz/www/static/imgs/no_login_v3.png' }}" width="35" height="35">
                </a>
            @else
                <a href="{{ route('public.member.login') }}" title="{{ __('Login') }}">
                    <img class="nav_user_img" alt="{{ __('User') }}" src="https://static.apkpures.xyz/www/static/imgs/no_login_v3.png" width="35" height="35">
                </a>
            @endauth
        </div>
    </div>
</header>
