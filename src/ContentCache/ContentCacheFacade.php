<?php

namespace InfusionWeb\Laravel\ContentCache;

use Illuminate\Support\Facades\Facade;

/**
 * @see \App\ContentCache\ContentCache
 */
class ContentCacheFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'contentcache';
    }
}
