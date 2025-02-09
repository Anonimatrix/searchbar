<?php

namespace Kompo\Searchbar;

use Kompo\Searchbar\SearchService;
use Kompo\Searchbar\SearchItems\Stores\SearchStore;
use Illuminate\Support\ServiceProvider;

class SearchbarServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SearchStore::class, function ($_, $params) {
            return (new (config('searchbar.store'))($params['key']))->injectContext($params['contextService']);
        });

        // Enhanced in \App\Kompo\Helpers\searchbar.php 
        $this->app->singleton('search-service', function() {
            return new SearchService();
        });
    }

    public function boot(): void
    {
        $this->loadConfig();
        
        $this->loadPublishing();

        $this->loadHelpers();
    }

    protected function loadHelpers()
    {
        $helpersDir = __DIR__.'/Helpers';

        $autoloadedHelpers = collect(\File::allFiles($helpersDir))->map(fn($file) => $file->getRealPath());

        $packageHelpers = [
        ];

        $autoloadedHelpers->concat($packageHelpers)->each(function ($path) {
            if (file_exists($path)) {
                require_once $path;
            }
        });
    }

    protected function loadConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/searchbar.php', 'searchbar');
    }

    protected function loadPublishing()
    {
        $this->publishes([
            __DIR__.'/../config/searchbar.php' => config_path('searchbar.php'),
        ], 'searchbar-config');
    }
}
