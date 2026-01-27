@if (Theme::breadcrumb()->getCrumbs())
    <div class="breadcrumb-wrap">
        <div class="breadcrumb-container">
            <nav class="breadcrumb" aria-label="{{ __('Breadcrumb') }}">
                @foreach (Theme::breadcrumb()->getCrumbs() as $crumb)
                    @if ($crumb['label'])
                        @if (!$loop->last && $crumb['url'])
                            <a href="{{ $crumb['url'] }}" class="breadcrumb-item">{{ $crumb['label'] }}</a>
                            <span class="separator">/</span>
                        @else
                            <span class="breadcrumb-item current">{{ $crumb['label'] }}</span>
                        @endif
                    @endif
                @endforeach
            </nav>
        </div>
    </div>
@endif
