{{-- APKPure App Download Widget --}}
<div class="sidebar-widget apkpure-app-widget">
    <div class="widget-icon">
        <img src="{{ theme_option('apkpure_app_icon', 'https://static.apkpure.com/www/static/imgs/apkpure_icon.png') }}" alt="APKPure" width="56" height="56">
    </div>
    <div class="widget-content">
        <h3>{{ __('APKPure App') }}</h3>
        <p>{{ __('Get the latest apps, faster and safer') }}</p>
        <a href="{{ theme_option('apkpure_app_download_url', '#') }}" class="download-btn">{{ __('Download') }}</a>
    </div>
</div>
