<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ac_apps', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->text('images')->nullable();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('requires_android_os')->nullable();
            $table->date('lasted_update')->nullable();
            $table->string('platform');
            $table->string('google_play')->nullable();
            $table->foreignId('lasted_version_id')->nullable()->index();
            $table->foreignId('scrape_ref_id')->nullable()->index();
            $table->foreignId('developer_id')->index();
            $table->timestamps();
        });

        Schema::create('ac_apps_translations', function (Blueprint $table): void {
            $table->string('lang_code', 20);
            $table->foreignId('apps_id');
            $table->string('description', 400)->nullable();
            $table->longText('content')->nullable();

            $table->primary(['lang_code', 'apps_id'], 'ac_apps_translations_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apps');
        Schema::dropIfExists('ac_apps');
    }
};
