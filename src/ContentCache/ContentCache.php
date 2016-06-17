<?php

namespace InfusionWeb\Laravel\ContentCache;

use Cache;

class ContentCache
{

    protected $profiles = [];

    protected $current_profile = '';

    public function profile($name)
    {
        $name = strtolower($name);

        if (!array_key_exists($name, $this->profiles)) {
            $this->profiles[$name] = new Profile($name);
        }

        $this->current_profile = $name;

        return $this;
    }

    protected function getProfile($name = '')
    {
        if (!$name) {
            $name = $this->current_profile;
        }

        if (array_key_exists($name, $this->profiles)) {
            return $this->profiles[$name];
        }
    }

    protected function getContent($name = '')
    {
        $profile = $this->getProfile($name);

        $client = \Kozz\Laravel\Facades\Guzzle::getFacadeRoot();

        $response = $client->get( $profile->getEndpoint(), ['query' => $profile->getQuery()] );

        $results = json_decode($response->getBody());

        // Run profile filters on results.
        $profile->filter($results);

        // Create derivitive fields on result objects.
        $profile->field($results);

        return $results;
    }

    public function cache($name = '')
    {
        if (!$name) {
            $name = $this->current_profile;
        }

        $items = $this->getContent($name);

        $minutes = config("content.cache.{$name}.minutes", 60);

        Cache::put("{$name}:count", count($items), $minutes);
        Cache::put("{$name}:all", $items, $minutes);

        $keys = config("content.cache.{$name}.keys", ['slug']);

        // Cache each content item, keyed by each key listed in configuration.
        foreach ($items as $item) {
            foreach ((array) $keys as $key) {
                if (property_exists($item, $key)) {
                    Cache::put("{$name}:{$key}:{$item->$key}", $item, $minutes);
                }
            }
        }

        return $this;
    }

    public function getAll($name = '')
    {
        if (!$name) {
            $name = $this->current_profile;
        }

        $items = (array) Cache::get("{$name}:all");

        return $items;
    }

    public function getBy($key, $value, $name = '')
    {
        if (!$name) {
            $name = $this->current_profile;
        }

        if (is_array($value)) {
            $items = [];

            foreach ($value as $val) {
                $items[$v] = Cache::get("{$name}:{$key}:{$val}");
            }

            return $items;
        }

        return Cache::get("{$name}:{$key}:{$value}");
    }

    public function __call($name, $arguments)
    {
        if (substr($name, 0, 5) == 'getBy') {
            $key = lcfirst(substr($name, 5));

            // Getting a little tricky here, but it works.
            list($value, $name) = array_merge($arguments, ['']);

            return $this->getBy($key, $value, $name);
        }

        throw new \BadMethodCallException("The method '$name' does not exist");
    }

}
