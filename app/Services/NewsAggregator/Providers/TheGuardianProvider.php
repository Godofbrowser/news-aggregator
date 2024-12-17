<?php


namespace App\Services\NewsAggregator\Providers;


use App\Services\NewsAggregator\AbstractProvider;
use App\Services\NewsAggregator\NewsFetchResult;
use App\Services\NewsAggregator\NewsFetchResultData;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class TheGuardianProvider extends AbstractProvider
{

    public function getIdentifier(): string
    {
        return 'the_guardian';
    }

    /**
     * Fetch news content from the guardian api
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
            $data = $this->request('GET', 'search', [
                'query' => [
                    'lang' => 'en',
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
        if (Arr::get($data, 'response.status') !== 'ok') {
            return new NewsFetchResult([], $page, $per_page, $page);
        }

        $newsData = Collection::make(Arr::get($data, 'response.results'))->map(function ($responseData) {
            $resultData = new NewsFetchResultData();
            $resultData->provider = $this->getIdentifier();
            $resultData->provider_id = $responseData['id'];
            $resultData->category_name = $responseData['sectionName'];
            $resultData->link = $responseData['webUrl'];
            $resultData->thumbnail = Arr::get($responseData, 'fields.thumbnail');
            $resultData->headline = $responseData['webTitle'];
            $resultData->body = Arr::get($responseData, 'fields.body');
            $resultData->published_at = Carbon::parse($responseData['webPublicationDate']);
            return $resultData;
        });

        return new NewsFetchResult(
            $newsData->toArray(),
            Arr::get($data, 'response.currentPage'),
            Arr::get($data, 'response.pageSize'),
            Arr::get($data, 'response.pages'),
        );
    }

    /**
     * Make an authorized request to the guardian api
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
            'base_uri' => 'https://content.guardianapis.com/',
            'timeout' => 5.0,
        ]);

        $baseQueryParams = [
            'api-key' => Config::get('services.news.providers.the_guardian.key'),
            'show-fields' => implode(',', ['thumbnail', 'body']),
            'show-references' => implode(',', ['author']),
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
