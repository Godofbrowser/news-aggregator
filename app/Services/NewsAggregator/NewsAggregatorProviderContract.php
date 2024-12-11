<?php


namespace App\Services\NewsAggregator;


use Closure;

interface NewsAggregatorProviderContract
{
    public function getIdentifier(): string;
    public function fetchNews(?int $page, ?int $per_page): NewsFetchResult;

    /**
     * Fetch news content from the api in batches
     *
     * @param $batchSize
     * @param Closure $closure
     * @param int $pageLimit
     * @author  Emeke Ajeh <ajemeke@gmail.com>
     */
    public function chunk($batchSize, Closure $closure, int $pageLimit): void;
}
