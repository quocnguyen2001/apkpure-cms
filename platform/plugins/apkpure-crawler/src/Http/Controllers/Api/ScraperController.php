<?php

namespace Wallis\ApkpureCrawler\Http\Controllers\Api;

use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Wallis\ApkpureCrawler\Http\Requests\ExtractRequest;
use Wallis\ApkpureCrawler\Http\Requests\ScrapeRequest;
use Wallis\ApkpureCrawler\Jobs\ScrapeUrlJob;
use Wallis\ApkpureCrawler\Models\ScrapeJob;
use Wallis\ApkpureCrawler\Services\Scraper\ScraperService;

class ScraperController extends BaseController
{
    public function __construct(
        private ScraperService $scraper
    ) {
    }

    public function scrape(ScrapeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $job = ScrapeJob::query()->create([
            'url' => $validated['url'],
            'options' => $validated['options'] ?? [],
        ]);

        if ($validated['async'] ?? false) {
            ScrapeUrlJob::dispatch($job);

            return response()->json([
                'job_id' => $job->id,
                'status' => 'queued',
            ], 202);
        }

        try {
            $result = $this->scraper->scrape(
                $validated['url'],
                $validated['options'] ?? []
            );

            return response()->json([
                'job_id' => $result->id,
                'status' => $result->status,
                'content' => $result->content,
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    public function status(ScrapeJob $job): JsonResponse
    {
        return response()->json([
            'id' => $job->id,
            'url' => $job->url,
            'status' => $job->status,
            'created_at' => $job->created_at,
            'started_at' => $job->started_at,
            'completed_at' => $job->completed_at,
            'content' => $job->content,
        ]);
    }

    public function extract(ExtractRequest $request, ScrapeJob $job): JsonResponse
    {
        if (! $job->content) {
            return response()->json([
                'error' => 'No content available',
            ], 404);
        }

        $data = $this->scraper->extractData(
            $job->content,
            $request->validated()['selectors']
        );

        return response()->json(['data' => $data]);
    }
}
