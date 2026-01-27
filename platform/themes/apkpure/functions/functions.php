<?php

use Botble\Media\Facades\RvMedia;
use Botble\Theme\Supports\ThemeSupport;

register_page_template([
    'default' => __('Default'),
]);

register_sidebar([
    'id' => 'primary_sidebar',
    'name' => __('Primary Sidebar'),
    'description' => __('Widgets for app detail pages'),
]);

app()->booted(function () {
    // Media sizes for app icons
    RvMedia::addSize('app-icon', 72, 72)
        ->addSize('app-icon-large', 120, 120)
        ->addSize('screenshot', 320, 569)
        ->addSize('banner', 868, 170);

    // Theme supports
    ThemeSupport::registerSocialLinks();
    ThemeSupport::registerToastNotification();
    ThemeSupport::registerPreloader();
    ThemeSupport::registerSiteCopyright();
    ThemeSupport::registerDateFormatOption();
    ThemeSupport::registerLazyLoadImages();
    ThemeSupport::registerSocialSharing();
    ThemeSupport::registerSiteLogoHeight();
});
