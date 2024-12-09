<?php


namespace App\Services\NewsAggregator;


use App\Services\NewsAggregator\Providers\TheGuardianProvider;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class NewsAggregatorServiceProvider extends ServiceProvider implements DeferrableProvider
{
    private array $providers = [
        TheGuardianProvider::class,
        // NewsAPIProvider::class,
        // OpenNewsProvider::class,
        // NewsCredProvider::class,
        // TheGuardianProvider::class,
        // NYTimesProvider::class,
        // BBCNewsProvider::class,
        // NewsAPIOrgProvider::class,
    ];

    /**
     * Register News Aggregator services.
     *
     * @author  Emeke Ajeh <ajemeke@gmail.com>
     */
    public function register(): void
    {
        $this->app->singleton(
            NewsAggregatorContract::class,
            NewsAggregatorService::class
        );
    }

    public function boot(Application $app): void {
        Collection::make($this->providers)->each(function ($class) use($app) {
            try {
                $service = $app->make(NewsAggregatorContract::class);
                /** @var NewsAggregatorProviderContract $instance */
                $instance = $app->make($class);
                $service->registerProvider($instance->getIdentifier(), $instance);
            } catch (BindingResolutionException $e) {
                Log::debug($e->getMessage(), [$e]);
            }
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     *
     * @author  Emeke Ajeh <ajemeke@gmail.com>
     */
    public function provides()
    {
        return [NewsAggregatorContract::class];
    }
}
