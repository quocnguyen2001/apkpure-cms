<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('app_versions', function (Blueprint $table): void {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
