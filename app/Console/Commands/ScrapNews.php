<?php

namespace App\Console\Commands;

use App\Services\NewsAggregator\NewsAggregatorContract;
use App\Services\NewsAggregator\NewsFetchResult;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

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
        // If batch size is greater than the provider's limit,
        // it will fallback to min for the platform
        $batchSize = 2;

        $this->comment('Scrapping started...');
        $this->comment(sprintf('Available providers: %s', implode(', ', $newsAggregator->getProviders())));
        $this->comment(sprintf('Enabled providers: %s', implode(', ', $newsAggregator->getEnabledProviders())));

        Collection::make($newsAggregator->getEnabledProviders())->each(function ($provider) use($newsAggregator, $batchSize) {
            $this->comment(sprintf('[%s] provider started', $provider));
            $content = [];
            $newsAggregator->withProvider($provider)
                ->chunk(
                    $batchSize,
                    function(NewsFetchResult $result) use(&$content) {
                        $content = array_merge($content, $result->getData());
                    }
                );
            $this->comment(sprintf('[%s] provider completed with %d content', $provider, count($content)));
        });


        $this->comment('Scrapping ended!');
        return Command::SUCCESS;
    }
}
