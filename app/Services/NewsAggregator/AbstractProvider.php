<?php


namespace App\Services\NewsAggregator;


use Closure;

abstract class AbstractProvider implements NewsAggregatorProviderContract
{

    abstract public function getIdentifier(): string;

    abstract public function fetchNews(?int $page, ?int $per_page): NewsFetchResult;

    /**
     * Fetch news content from the api in batches
     *
     * @param $batchSize
     * @param Closure $closure
     * @author  Emeke Ajeh <ajemeke@gmail.com>
     */
    public function chunk($batchSize, Closure $closure): void {
        $result = null;
        while (true) {
            $result = $this->fetchNews($result ? $result->getPage() + 1 : 1, $batchSize);
            call_user_func($closure, $result);
            logger([$result, $result->hasNextPage(), $result->getPage()]);
            if (!$result->hasNextPage() || $result->getPage() >= 5) break;
            sleep(0.2);
        }
    }
}
