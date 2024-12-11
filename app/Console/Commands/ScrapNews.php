<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use App\Services\NewsAggregator\NewsAggregatorContract;
use App\Services\NewsAggregator\NewsFetchResult;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScrapNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:news';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap and store news from multiple sources';

    /**
     * Execute the console command.
     *
     * @param NewsAggregatorContract $newsAggregator
     * @return int
     */
    public function handle(NewsAggregatorContract $newsAggregator)
    {
//        if (Cache::has('news_data')) {
//            $this->comment('Cache hit...');
//            $this->persistContent(Cache::get('news_data'));
//            return Command::SUCCESS;
//        }

        // If batch size is greater than the provider's limit,
        // it will fallback to min for the platform
        $batchSize = 20;
        $pageLimit = 10; // Let's not go beyound 20 pages for this testing purpose

        $this->comment('Scrapping started...');
        $this->comment(sprintf('Available providers: [%s]', implode(', ', $newsAggregator->getProviders())));
        $this->comment(sprintf('Enabled providers: [%s]', implode(', ', $newsAggregator->getEnabledProviders())));

        Collection::make($newsAggregator->getEnabledProviders())->each(function ($provider) use($newsAggregator, $batchSize, $pageLimit) {
            $this->comment(sprintf('provider [%s] started', $provider));
            $contentCount = 0;
            $newsAggregator->withProvider($provider)
                ->chunk(
                    $batchSize,
                    function(NewsFetchResult $result) use(&$contentCount, $provider) {
                        $content = $result->getData();
                        $contentCount += count($content);
                        $this->persistContent($content);
                        $this->comment(sprintf('provider [%s] batched. Page: %s', $provider, $result->getPage()));
                    },
                    $pageLimit,
                );
//            Log::debug('Content: ', [$content]);
//            Cache::put('news_data', $content, now()->addMinutes(60));
//            $this->persistContent($content);
            $this->comment(sprintf('provider [%s] completed with %d content', $provider, $contentCount));
        });


        $this->comment('Scrapping ended!');
        return Command::SUCCESS;
    }

    private function persistContent($content): void {
        // Get category names from the list of articles
        $catNames = Collection::make($content)->map(function ($cat) {
            return $cat->category_name;
        })->unique()
            ->filter(fn($cat) => !is_null($cat))
            ->map(fn($cat) => ['name' => $cat])->toArray();

        // Synchronize the categories table
        Category::query()->upsert($catNames, ['name'], ['name']);

        // Prepare a fallback category
        $genericCategoryModel = Category::query()->first();

        // Prepare a dictionary of "article category name" and "db category ID"
        $catNameKeyMap = Category::query()
            ->whereIn('name', $catNames)
            ->get(['name', 'id'])
            ->keyBy('name')
            ->map(fn($item) => $item->getKey());

        // Synchronize the articles table
        Article::query()
            ->upsert(
                Collection::make($content)
                    ->map(function ($item) use ($catNameKeyMap, $genericCategoryModel) {
                        $category_id = $catNameKeyMap[$item->category_name ?: ''] ?? $genericCategoryModel->getKey();
                        unset($item->category_name);
                        return array_merge($item->toArray(), ['category_id' => $category_id]);
                    })->toArray(), ['provider_id', 'provider'], []
            );
    }
}
