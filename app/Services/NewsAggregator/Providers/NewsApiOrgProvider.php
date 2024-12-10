<?php


namespace App\Services\NewsAggregator\Providers;


use App\Services\NewsAggregator\AbstractProvider;
use App\Services\NewsAggregator\NewsFetchResult;
use App\Services\NewsAggregator\NewsFetchResultData;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewsApiOrgProvider extends AbstractProvider
{
    public function getIdentifier(): string
    {
        return 'news_api_org';
    }

    /**
     * Fetch news content from the NewsAPiOrg api
     * Docs - https://newsapi.org/docs/endpoints/everything
     *
     * @param int|null $page
     * @param int|null $per_page
     * @return NewsFetchResult
     * @throws GuzzleException
     * @author  Emeke Ajeh <ajemeke@gmail.com>
     */
    public function fetchNews(?int $page, ?int $per_page): NewsFetchResult
    {
        $endpoints = ['top' => 'top-headlines', 'search' => 'everything'];

        try {
            $data = $this->request('GET', $endpoints['top'], [
                'query' => [
                    'pageSize' => min(100, $per_page),
                    'page' => $page,
                    'sources' => Collection::make($this->getSources())->random(20)->map(fn($s) => $s['id'])->join(',')
                ]
            ]);

            return $this->transformFetchNewsResult($data, $page, $per_page);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [$e]);
        }
        return new NewsFetchResult([], $page, $per_page, $page);
    }

    private function transformFetchNewsResult($data, $page, $per_page): NewsFetchResult {
        if ($data['status'] !== 'ok') {
            return new NewsFetchResult([], $page, $per_page, $page);
        }

        $totalResults = $data['totalResults'];
        $totalPages = intval(ceil($totalResults / $per_page));

        $newsData = Collection::make($data['articles'])
            ->filter(fn($r) => !is_null($r['source']['id']))
            ->map(function ($responseData) {
                $resultData = new NewsFetchResultData();
                $resultData->provider = $this->getIdentifier();
                $resultData->provider_id = sprintf('%s#%s', $responseData['source']['id'], Str::slug($responseData['title']));
                $resultData->category_name = null;
                $resultData->link = $responseData['url'];
                $resultData->thumbnail = $responseData['urlToImage'];
                $resultData->headline = $responseData['title'];
                $resultData->body = $responseData['content'] ?: '';
                // $resultData->author = $responseData['author'];
                $resultData->published_at = Carbon::parse($responseData['publishedAt']);
                return $resultData;
            });

        return new NewsFetchResult(
            $newsData->toArray(),
            $page,
            $per_page,
            $totalPages,
        );
    }

    private function getSources()
    {
        $cacheKey = sprintf('news-provider#%s#sources', $this->getIdentifier());
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = $this->request('GET', 'sources', []);
        if ($response['status'] === 'ok') {
            Cache::put($cacheKey, $response['sources'], now()->addDay());
            return Cache::get($cacheKey);
        }
        return [];
    }

    /**
     * Make an authorized request to the NewsAPI.org api
     *
     * @param $method
     * @param $endpoint
     * @param $options
     * @return array
     * @throws GuzzleException
     * @author  Emeke Ajeh <ajemeke@gmail.com>
     */
    private function request($method, $endpoint, $options): array {
        $client = new Client([
            'base_uri' => 'https://newsapi.org/v2/',
            'timeout' => 5.0,
        ]);

        $baseQueryParams = [
            'apiKey' => Config::get('services.news.providers.news_api_org.key'),
        ];

        $baseOptions = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'query' => $baseQueryParams,
        ];

        try {
            $response = $client->request($method, $endpoint, array_merge_recursive_distinct($baseOptions, $options));
            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            Log::error($e->getMessage(), [$e]);
            throw $e;
        }
    }
}
