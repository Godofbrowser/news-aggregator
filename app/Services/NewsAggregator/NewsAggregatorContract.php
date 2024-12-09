<?php


namespace App\Services\NewsAggregator;


interface NewsAggregatorContract
{
    /**
     * Register a news aggregator provider
     *
     * @param string $identifier
     * @param NewsAggregatorProviderContract $provider
     * @return $this
     *
     * @author  Emeke Ajeh <ajemeke@gmail.com>
     */
    public function registerProvider(string $identifier, NewsAggregatorProviderContract $provider): self;

    /**
     * Get instance of a news aggregator provider
     *
     * @param string $identifier
     * @return NewsAggregatorProviderContract
     *
     * @author  Emeke Ajeh <ajemeke@gmail.com>
     */
    public function withProvider(string $identifier): NewsAggregatorProviderContract;

    /**
     * Get the list of available providers
     *
     * @return string[]
     *
     * @author  Emeke Ajeh <ajemeke@gmail.com>
     */
    public function getProviders(): array;

    /**
     * Get the list of available providers that are enabled in the config
     *
     * @return string[]
     *
     * @author  Emeke Ajeh <ajemeke@gmail.com>
     */
    public function getEnabledProviders(): array;
}
