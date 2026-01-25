<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ac_app_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('app_id')->index();
            $table->string('version');
            $table->text('changelog')->nullable();
            $table->date('release_date');
            $table->integer('file_size');
            $table->string('file_path');
            $table->string('storage_disk');
            $table->string('origin_download_url');
            $table->foreignId('scrape_ref_id')->index();
            $table->timestamps();
        });

        Schema::create('ac_app_versions_translations', function (Blueprint $table): void {
            $table->string('lang_code', 20);
            $table->foreignId('app_versions_id');
            $table->longText('changelog')->nullable();

            $table->primary(['lang_code', 'app_versions_id'], 'ac_app_versions_translations_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ac_app_versions');
        Schema::dropIfExists('ac_app_versions_translations');
    }
};
