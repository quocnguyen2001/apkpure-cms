<?php

namespace Database\Seeders;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseSeeder;
use Illuminate\Support\Facades\DB;
use Wallis\ApkpureCrawler\Enums\AppPlatformEnum;
use Wallis\ApkpureCrawler\Models\App;
use Wallis\ApkpureCrawler\Models\AppCategory;
use Wallis\ApkpureCrawler\Models\AppTag;
use Wallis\ApkpureCrawler\Models\AppVersion;
use Wallis\ApkpureCrawler\Models\Developer;

class ApkpureSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->uploadFiles('apkpure');

        $this->createDevelopers();
        $this->createCategories();
        $this->createTags();
        $this->createApps();
    }

    protected function createDevelopers(): void
    {
        Developer::query()->truncate();

        $developers = [
            [
                'name' => 'Google LLC',
                'website' => 'https://www.google.com',
                'logo' => $this->filePath('apkpure/developers/google.png'),
                'description' => 'Google LLC is an American multinational technology company focusing on artificial intelligence, online advertising, search engine technology, cloud computing, computer software.',
                'content' => 'Google LLC is an American multinational technology company that specializes in Internet-related services and products.',
                'status' => BaseStatusEnum::PUBLISHED,
            ],
            [
                'name' => 'Meta Platforms, Inc.',
                'website' => 'https://www.meta.com',
                'logo' => $this->filePath('apkpure/developers/meta.png'),
                'description' => 'Meta Platforms, Inc., doing business as Meta, is an American multinational technology conglomerate.',
                'content' => 'Meta Platforms, Inc. is the company behind Facebook, Instagram, WhatsApp, and other popular applications.',
                'status' => BaseStatusEnum::PUBLISHED,
            ],
            [
                'name' => 'Spotify AB',
                'website' => 'https://www.spotify.com',
                'logo' => $this->filePath('apkpure/developers/spotify.png'),
                'description' => 'Spotify is a Swedish audio streaming and media services provider.',
                'content' => 'Spotify Technology S.A. is a Swedish audio streaming and media services provider, launched in 2008.',
                'status' => BaseStatusEnum::PUBLISHED,
            ],
        ];

        foreach ($developers as $developer) {
            Developer::query()->create($developer);
        }
    }

    protected function createCategories(): void
    {
        AppCategory::query()->truncate();
        DB::table('ac_app_category')->truncate();

        $categories = [
            [
                'name' => 'Social',
                'description' => 'Social networking and communication apps',
                'logo' => $this->filePath('apkpure/categories/social.png'),
            ],
            [
                'name' => 'Entertainment',
                'description' => 'Entertainment and media apps',
                'logo' => $this->filePath('apkpure/categories/entertainment.png'),
            ],
            [
                'name' => 'Music & Audio',
                'description' => 'Music streaming and audio apps',
                'logo' => $this->filePath('apkpure/categories/music.png'),
            ],
            [
                'name' => 'Communication',
                'description' => 'Messaging and communication apps',
                'logo' => $this->filePath('apkpure/categories/communication.png'),
            ],
            [
                'name' => 'Video Players',
                'description' => 'Video players and editors',
                'logo' => $this->filePath('apkpure/categories/video.png'),
            ],
        ];

        foreach ($categories as $category) {
            AppCategory::query()->create($category);
        }
    }

    protected function createTags(): void
    {
        AppTag::query()->truncate();
        DB::table('ac_app_tag')->truncate();

        $tags = [
            ['name' => 'Free', 'description' => 'Free to use apps'],
            ['name' => 'Popular', 'description' => 'Most popular apps'],
            ['name' => 'Trending', 'description' => 'Currently trending apps'],
            ['name' => 'Editor Choice', 'description' => 'Selected by editors'],
            ['name' => 'New Release', 'description' => 'Recently released apps'],
            ['name' => 'Top Rated', 'description' => 'Highly rated apps'],
            ['name' => 'Offline', 'description' => 'Works offline'],
            ['name' => 'Dark Mode', 'description' => 'Supports dark mode'],
        ];

        foreach ($tags as $tag) {
            AppTag::query()->create($tag);
        }
    }

    protected function createApps(): void
    {
        App::query()->truncate();
        AppVersion::query()->truncate();

        $googleDeveloper = Developer::query()->where('name', 'Google LLC')->first();
        $metaDeveloper = Developer::query()->where('name', 'Meta Platforms, Inc.')->first();
        $spotifyDeveloper = Developer::query()->where('name', 'Spotify AB')->first();

        $apps = [
            [
                'name' => 'YouTube',
                'logo' => $this->filePath('apkpure/apps/youtube.png'),
                'images' => [
                    $this->filePath('apkpure/apps/youtube-1.png'),
                    $this->filePath('apkpure/apps/youtube-2.png'),
                ],
                'description' => 'Watch, upload and share videos',
                'content' => 'YouTube is the official app of the world\'s largest and most popular video platform. It\'s home to millions of videos covering every topic from gaming to music, news to entertainment.',
                'requires_android_os' => 'Android 8.0+',
                'lasted_update' => now()->subDays(3),
                'platform' => AppPlatformEnum::ANDROID,
                'google_play' => 'https://play.google.com/store/apps/details?id=com.google.android.youtube',
                'developer_id' => $googleDeveloper->id,
                'categories' => ['Entertainment', 'Video Players'],
                'tags' => ['Free', 'Popular', 'Top Rated'],
                'versions' => [
                    [
                        'version' => '19.50.40',
                        'changelog' => 'Bug fixes and performance improvements',
                        'release_date' => now()->subDays(3),
                        'file_size' => 52428800,
                        'file_path' => 'apkpure/apks/youtube-19.50.40.apk',
                        'storage_disk' => 'public',
                        'origin_download_url' => 'https://apkpure.com/youtube/com.google.android.youtube',
                    ],
                    [
                        'version' => '19.49.36',
                        'changelog' => 'New features and bug fixes',
                        'release_date' => now()->subDays(10),
                        'file_size' => 51380224,
                        'file_path' => 'apkpure/apks/youtube-19.49.36.apk',
                        'storage_disk' => 'public',
                        'origin_download_url' => 'https://apkpure.com/youtube/com.google.android.youtube',
                    ],
                ],
            ],
            [
                'name' => 'Instagram',
                'logo' => $this->filePath('apkpure/apps/instagram.png'),
                'images' => [
                    $this->filePath('apkpure/apps/instagram-1.png'),
                    $this->filePath('apkpure/apps/instagram-2.png'),
                ],
                'description' => 'Share photos and videos with friends',
                'content' => 'Instagram is a free photo and video sharing app. People use Instagram to share photos and videos with friends and family, discover new content from creators around the world, and connect with communities they care about.',
                'requires_android_os' => 'Android 8.0+',
                'lasted_update' => now()->subDays(5),
                'platform' => AppPlatformEnum::ANDROID,
                'google_play' => 'https://play.google.com/store/apps/details?id=com.instagram.android',
                'developer_id' => $metaDeveloper->id,
                'categories' => ['Social'],
                'tags' => ['Free', 'Popular', 'Trending'],
                'versions' => [
                    [
                        'version' => '330.0.0.0.94',
                        'changelog' => 'Improved Reels experience and bug fixes',
                        'release_date' => now()->subDays(5),
                        'file_size' => 68157440,
                        'file_path' => 'apkpure/apks/instagram-330.apk',
                        'storage_disk' => 'public',
                        'origin_download_url' => 'https://apkpure.com/instagram/com.instagram.android',
                    ],
                ],
            ],
            [
                'name' => 'WhatsApp Messenger',
                'logo' => $this->filePath('apkpure/apps/whatsapp.png'),
                'images' => [
                    $this->filePath('apkpure/apps/whatsapp-1.png'),
                    $this->filePath('apkpure/apps/whatsapp-2.png'),
                ],
                'description' => 'Simple. Reliable. Private messaging.',
                'content' => 'WhatsApp Messenger is a FREE messaging app available for Android and other smartphones. WhatsApp uses your phone\'s Internet connection to let you message and call friends and family.',
                'requires_android_os' => 'Android 5.0+',
                'lasted_update' => now()->subDays(2),
                'platform' => AppPlatformEnum::ANDROID,
                'google_play' => 'https://play.google.com/store/apps/details?id=com.whatsapp',
                'developer_id' => $metaDeveloper->id,
                'categories' => ['Communication', 'Social'],
                'tags' => ['Free', 'Popular', 'Top Rated', 'Dark Mode'],
                'versions' => [
                    [
                        'version' => '2.24.25.80',
                        'changelog' => 'Security updates and performance improvements',
                        'release_date' => now()->subDays(2),
                        'file_size' => 73400320,
                        'file_path' => 'apkpure/apks/whatsapp-2.24.25.80.apk',
                        'storage_disk' => 'public',
                        'origin_download_url' => 'https://apkpure.com/whatsapp-messenger/com.whatsapp',
                    ],
                ],
            ],
            [
                'name' => 'Spotify',
                'logo' => $this->filePath('apkpure/apps/spotify.png'),
                'images' => [
                    $this->filePath('apkpure/apps/spotify-1.png'),
                    $this->filePath('apkpure/apps/spotify-2.png'),
                ],
                'description' => 'Listen to music, podcasts and more',
                'content' => 'Spotify is a digital music service that gives you access to millions of songs, podcasts, and videos from artists all over the world.',
                'requires_android_os' => 'Android 6.0+',
                'lasted_update' => now()->subDays(7),
                'platform' => AppPlatformEnum::ANDROID,
                'google_play' => 'https://play.google.com/store/apps/details?id=com.spotify.music',
                'developer_id' => $spotifyDeveloper->id,
                'categories' => ['Music & Audio', 'Entertainment'],
                'tags' => ['Free', 'Popular', 'Editor Choice', 'Offline', 'Dark Mode'],
                'versions' => [
                    [
                        'version' => '8.9.32.600',
                        'changelog' => 'New features for podcasts and improved recommendations',
                        'release_date' => now()->subDays(7),
                        'file_size' => 41943040,
                        'file_path' => 'apkpure/apks/spotify-8.9.32.600.apk',
                        'storage_disk' => 'public',
                        'origin_download_url' => 'https://apkpure.com/spotify/com.spotify.music',
                    ],
                ],
            ],
            [
                'name' => 'Google Maps',
                'logo' => $this->filePath('apkpure/apps/maps.png'),
                'images' => [
                    $this->filePath('apkpure/apps/maps-1.png'),
                    $this->filePath('apkpure/apps/maps-2.png'),
                ],
                'description' => 'Navigate your world faster and easier',
                'content' => 'Google Maps is the best way to explore and navigate the world. Find nearby restaurants, get real-time traffic updates, and discover local businesses with Google Maps.',
                'requires_android_os' => 'Android 8.0+',
                'lasted_update' => now()->subDays(1),
                'platform' => AppPlatformEnum::ANDROID,
                'google_play' => 'https://play.google.com/store/apps/details?id=com.google.android.apps.maps',
                'developer_id' => $googleDeveloper->id,
                'categories' => ['Entertainment'],
                'tags' => ['Free', 'Popular', 'Top Rated', 'Offline'],
                'versions' => [
                    [
                        'version' => '11.130.0100',
                        'changelog' => 'Improved navigation and offline maps',
                        'release_date' => now()->subDays(1),
                        'file_size' => 104857600,
                        'file_path' => 'apkpure/apks/maps-11.130.0100.apk',
                        'storage_disk' => 'public',
                        'origin_download_url' => 'https://apkpure.com/google-maps/com.google.android.apps.maps',
                    ],
                    [
                        'version' => '11.129.0102',
                        'changelog' => 'Bug fixes',
                        'release_date' => now()->subDays(8),
                        'file_size' => 104000512,
                        'file_path' => 'apkpure/apks/maps-11.129.0102.apk',
                        'storage_disk' => 'public',
                        'origin_download_url' => 'https://apkpure.com/google-maps/com.google.android.apps.maps',
                    ],
                ],
            ],
        ];

        foreach ($apps as $appData) {
            $categories = $appData['categories'] ?? [];
            $tags = $appData['tags'] ?? [];
            $versions = $appData['versions'] ?? [];

            unset($appData['categories'], $appData['tags'], $appData['versions']);

            $app = App::query()->create($appData);

            $lastedVersionId = null;
            foreach ($versions as $index => $versionData) {
                $versionData['app_id'] = $app->id;
                $versionData['scrape_ref_id'] = 0;
                $version = AppVersion::query()->create($versionData);

                if ($index === 0) {
                    $lastedVersionId = $version->id;
                }
            }

            if ($lastedVersionId) {
                $app->update(['lasted_version_id' => $lastedVersionId]);
            }

            if ($categories) {
                $categoryIds = AppCategory::query()
                    ->whereIn('name', $categories)
                    ->pluck('id')
                    ->toArray();
                $app->categories()->attach($categoryIds);
            }

            if ($tags) {
                $tagIds = AppTag::query()
                    ->whereIn('name', $tags)
                    ->pluck('id')
                    ->toArray();
                $app->tags()->attach($tagIds);
            }
        }
    }
}
