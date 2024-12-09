<?php


namespace App\Services\NewsAggregator;


use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

class NewsAggregatorServiceProvider extends ServiceProvider implements DeferrableProvider
{
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
        $this->loadProviders();
    }

    public function boot(): void {
        $this->loadProviders();
    }

    private function registerProvider(string $provider): void {
       try {
           $service = $this->app->make(NewsAggregatorContract::class);
           /** @var NewsAggregatorProviderContract $instance */
           $instance = $this->app->make($provider);
           $service->registerProvider($instance->getIdentifier(), $instance);
       } catch (BindingResolutionException $e) {
           Log::debug($e->getMessage(), [$e]);
       }
    }


    /**
     * Register all of the Providers in the /Providers directory.
     *
     * @return void
     */
    private function loadProviders()
    {
        $namespace = $this->app->getNamespace();

        foreach ((new Finder)->in(__DIR__.'/Providers')->files() as $provider) {
            $provider = $namespace.str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($provider->getRealPath(), realpath(app_path()).DIRECTORY_SEPARATOR)
                );

            try {
                if (is_subclass_of($provider, AbstractProvider::class) &&
                    !(new ReflectionClass($provider))->isAbstract()) {
                    $this->registerProvider($provider);
                }
            } catch (\ReflectionException $e) {
                Log::error($e->getMessage(), [$e]);
            }
        }
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
