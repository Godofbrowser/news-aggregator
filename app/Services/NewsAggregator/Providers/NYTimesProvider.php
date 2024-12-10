<?php


namespace App\Services\NewsAggregator\Providers;


use App\Services\NewsAggregator\AbstractProvider;
use App\Services\NewsAggregator\NewsFetchResult;
use App\Services\NewsAggregator\NewsFetchResultData;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class NYTimesProvider extends AbstractProvider
{

    public function getIdentifier(): string
    {
        return 'ny_times';
    }

    /**
     * Fetch news content from the ny-times api
     * Docs - https://open-platform.theguardian.com/documentation
     *
     * @param int|null $page
     * @param int|null $per_page
     * @return NewsFetchResult
     * @throws GuzzleException
     * @author  Emeke Ajeh <ajemeke@gmail.com>
     */
    public function fetchNews(?int $page, ?int $per_page): NewsFetchResult
    {
        try {
            $data = $this->request('GET', 'articlesearch.json', [
                'query' => [
                    'page-size' => min(50, $per_page),
                    'page' => $page,
                ]
            ]);

            return $this->transformFetchNewsResult($data, $page, $per_page);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [$e]);
        }

        return new NewsFetchResult([], $page, $per_page, $page);
    }

    private function transformFetchNewsResult($data, $page, $per_page): NewsFetchResult {
        if ($data['status'] !== 'OK') {
            return new NewsFetchResult([], $page, $per_page, $page);
        }

        $totalResults = $data['response']['meta']['hits'];
        // Todo: how to change page size? API is missing
        $per_page = 10; // override
        $totalPages = intval(ceil($totalResults / $per_page));

        $newsData = Collection::make($data['response']['docs'])->map(function ($responseData) {
            $resultData = new NewsFetchResultData();
            $resultData->provider = $this->getIdentifier();
            $resultData->provider_id = $responseData['_id'];
            $resultData->category_name = $responseData['section_name'];
            $resultData->link = $responseData['web_url'];
            $resultData->thumbnail = sprintf('https://www.nytimes.com/%s', $responseData['multimedia'][0]['url']);
            $resultData->headline = $responseData['headline']['main'];
            $resultData->body = $responseData['abstract']; // todo: we need full content
            $resultData->published_at = Carbon::parse($responseData['pub_date']);
            return $resultData;
        });

        return new NewsFetchResult(
            $newsData->toArray(),
            $page,
            $per_page,
            $totalPages,
        );
    }

    /**
     * Make an authorized request to the NYTimes api
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
            'base_uri' => 'https://api.nytimes.com/svc/search/v2/',
            'timeout' => 5.0,
        ]);

        $baseQueryParams = [
            'api-key' => Config::get('services.news.providers.ny_times.key'),
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
