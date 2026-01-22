<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('app_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->index();
            $table->string('slug')->unique()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('logo')->nullable();
            $table->foreignId('scrape_ref_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('app_category', function (Blueprint $table): void {
            $table->foreignId('app_id');
            $table->foreignId('app_category_id');
            $table->primary(['app_id', 'app_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_category');
        Schema::dropIfExists('app_categories');
    }
};
