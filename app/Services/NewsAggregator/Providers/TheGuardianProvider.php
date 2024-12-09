<?php


namespace App\Services\NewsAggregator\Providers;


use App\Services\NewsAggregator\AbstractProvider;
use App\Services\NewsAggregator\NewsFetchResult;
use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
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
        $data = $this->request('GET', 'search', [
            'query' => [
                'q' => 'technology',
                'lang' => 'en',
                'page-size' => min(50, $per_page),
                'page' => $page,
            ]
        ]);

        if ($data['response']['status'] !== 'ok') {
            return new NewsFetchResult([], $page, $per_page, $page);
        }

        return new NewsFetchResult(
            $data['response']['results'],
            $data['response']['currentPage'],
            $data['response']['pageSize'],
            $data['response']['pages'],
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
//            'show-references' => implode(',', ['author']),
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
