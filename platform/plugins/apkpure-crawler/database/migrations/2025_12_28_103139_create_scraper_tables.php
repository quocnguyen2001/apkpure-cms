<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('scrape_jobs', function (Blueprint $table): void {
            $table->id();
            $table->string('url')->index();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('options')->nullable();
            $table->json('result')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('scraped_contents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('scrape_job_id')->constrained()->onDelete('cascade');
            $table->string('url')->index();
            $table->string('title')->nullable();
            $table->longText('html');
            $table->json('cookies')->nullable();
            $table->json('metadata')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scraped_contents');
        Schema::dropIfExists('scrape_jobs');
    }
};
