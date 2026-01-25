<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ac_app_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('logo')->nullable();
            $table->foreignId('scrape_ref_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('ac_app_categories_translations', function (Blueprint $table): void {
            $table->string('lang_code', 20);
            $table->foreignId('app_categories_id');
            $table->string('description', 400)->nullable();
            $table->longText('content')->nullable();

            $table->primary(['lang_code', 'app_categories_id'], 'ac_app_categories_translations_primary');
        });

        Schema::create('ac_app_category', function (Blueprint $table): void {
            $table->foreignId('app_id');
            $table->foreignId('app_category_id');
            $table->primary(['app_id', 'app_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ac_app_category');
        Schema::dropIfExists('ac_app_categories_translations');
        Schema::dropIfExists('ac_app_categories');
    }
};
