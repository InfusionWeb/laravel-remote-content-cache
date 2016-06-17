<?php

namespace InfusionWeb\Laravel\ContentCache;

use Illuminate\Support\ServiceProvider;

class ContentCacheServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerContentCache();

        $this->app->alias('contentcache', 'InfusionWeb\Laravel\ContentCache\ContentCache');
    }

    /**
     * Register the Marketo connection instance.
     *
     * @return void
     */
    protected function registerContentCache()
    {
        $this->app->singleton('contentcache', function($app)
        {
            return new ContentCache($app['url']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('contentcache', 'InfusionWeb\Laravel\ContentCache\ContentCache');
    }
}
