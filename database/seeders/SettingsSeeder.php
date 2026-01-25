<?php

declare(strict_types=1);

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Setting\Facades\Setting;

class SettingsSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->uploadFiles('general');

        $settings = [
            'admin_favicon' => 'general/favicon.png',
            'admin_logo' => 'general/logo.png',
            'admin_primary_color' => '#23b068',
            'admin_link_color' => '#23b068',
            'admin_link_hover_color' => '#23b068',
        ];

        Setting::delete(array_keys($settings));

        Setting::set($settings)->save();
    }
}
