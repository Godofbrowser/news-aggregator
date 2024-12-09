<?php


namespace App\Services\NewsAggregator;


use App\Services\NewsAggregator\Exceptions\ProviderResolutionException;
use Exception;
use Illuminate\Support\Facades\Config;

class NewsAggregatorService implements NewsAggregatorContract
{
    /**
     * @var array
     */
    private array $providers = [];

    /**
     * Register a news aggregator provider
     *
     * @param string $identifier
     * @param NewsAggregatorProviderContract $provider
     * @return $this
     *
     * @author  Emeke Ajeh <ajemeke@gmail.com>
     */
    public function registerProvider(string $identifier, NewsAggregatorProviderContract $provider): self {
        $this->providers[$identifier] = $provider;
        return $this;
    }

    /**
     * Get instance of a news aggregator provider
     *
     * @param string $identifier
     * @return NewsAggregatorProviderContract
     *
     * @author  Emeke Ajeh <ajemeke@gmail.com>
     */
    public function withProvider(string $identifier): NewsAggregatorProviderContract {
        try {
            return $this->providers[$identifier];
        } catch (Exception $exception) {
            throw ProviderResolutionException::forIdentifier($identifier, $exception);
        }
    }

    /**
     * Get the list of available providers
     *
     * @return string[]
     *
     * @author  Emeke Ajeh <ajemeke@gmail.com>
     */
    public function getProviders(): array {
        return array_keys($this->providers);
    }

    /**
     * Get the list of available providers that are enabled in the config
     *
     * @return string[]
     *
     * @author  Emeke Ajeh <ajemeke@gmail.com>
     */
    public function getEnabledProviders(): array {
        return array_intersect($this->getProviders(), Config::get('services.news.enabled_providers'));
    }
}
