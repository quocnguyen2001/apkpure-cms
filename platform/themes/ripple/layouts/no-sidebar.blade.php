{!! Theme::partial('header') !!}
<main>
    @if (Theme::get('section-name'))
        {!! Theme::partial('breadcrumbs') !!}
    @endif
    {!! Theme::content() !!}
</main>
{!! Theme::partial('footer') !!}
