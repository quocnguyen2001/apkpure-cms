<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ac_developers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->foreignId('scrape_ref_id')->nullable()->index();
            $table->string('status')->index();
            $table->timestamps();
        });

        Schema::create('ac_developers_translations', function (Blueprint $table): void {
            $table->string('lang_code', 20);
            $table->foreignId('developers_id');
            $table->string('description', 400)->nullable();
            $table->longText('content')->nullable();

            $table->primary(['lang_code', 'developers_id'], 'ac_developers_translations_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ac_developers');
        Schema::dropIfExists('ac_developers_translations');
    }
};
