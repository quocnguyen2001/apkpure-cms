<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {!! Theme::header() !!}
</head>
<body {!! Theme::bodyAttributes() !!}>
    {!! apply_filters(THEME_FRONT_BODY, null) !!}

    @include('theme.apkpure::partials.header')

    @hasSection('breadcrumbs')
        @yield('breadcrumbs')
    @endif

    <div class="main-body">
        <div class="left">
            @yield('content')
        </div>
        <div class="right">
            @hasSection('sidebar')
                @yield('sidebar')
            @else
                @include('theme.apkpure::partials.sidebar.apkpure-app-widget')
            @endif
        </div>
    </div>

    @include('theme.apkpure::partials.footer')

    {!! Theme::footer() !!}
</body>
</html>
