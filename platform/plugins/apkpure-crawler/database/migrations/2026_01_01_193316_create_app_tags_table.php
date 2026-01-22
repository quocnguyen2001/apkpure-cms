<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('app_tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->index();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('logo')->nullable();
            $table->foreignId('scrape_ref_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('app_tag', function (Blueprint $table): void {
            $table->foreignId('app_id');
            $table->foreignId('app_tag_id');
            $table->primary(['app_id', 'app_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_tag');
        Schema::dropIfExists('app_tags');
    }
};
