# Laravel Remote Content Cache
[![Latest Stable Version](https://poser.pugx.org/infusionweb/laravel-remote-content-cache/v/stable)](https://packagist.org/packages/infusionweb/laravel-remote-content-cache) [![Total Downloads](https://poser.pugx.org/infusionweb/laravel-remote-content-cache/downloads)](https://packagist.org/packages/infusionweb/laravel-remote-content-cache) [![Latest Unstable Version](https://poser.pugx.org/infusionweb/laravel-remote-content-cache/v/unstable)](https://packagist.org/packages/infusionweb/laravel-remote-content-cache) [![License](https://poser.pugx.org/infusionweb/laravel-remote-content-cache/license)](https://packagist.org/packages/infusionweb/laravel-remote-content-cache)

## A Laravel package for retrieving and caching content from remote servers.

This package provides a convenient way to retrieve content from remote servers and cache it for use in Laravel.

The assumption is that this process will be used to create a Laravel front-end for a headless CMS setup. However, there are certainly other use cases for which it should work equally well.

Configuration is handled entirely by a single Laravel config file.

## Installation

### Step 1: Composer

Via Composer command line:

```bash
$ composer require infusionweb/laravel-remote-content-cache
```

Or add the package to your `composer.json`:

```json
{
    "require": {
        "infusionweb/laravel-remote-content-cache": "~1.3.0"
    }
}
```

### Step 2: Register the Service Provider

Add the service provider to your `config/app.php`:

```php
'providers' => [
    //
    Kozz\Laravel\Providers\Guzzle::class,
    InfusionWeb\Laravel\ContentCache\ContentCacheServiceProvider::class,
];
```

### Step 3: Enable the Facade

Add the facade to your `config/app.php`:

```php
'aliases' => [
    //
    'Guzzle' => Kozz\Laravel\Facades\Guzzle::class,
    'ContentCache' => InfusionWeb\Laravel\ContentCache\ContentCacheFacade::class,
];
```

### Step 4: Publish the package config file

```bash
$ php artisan vendor:publish --provider="InfusionWeb\Laravel\ContentCache\ContentCacheServiceProvider"
```

You may now configure remote API endpoints for consumption and other preferences by editing the `config/contentcache.php` file.

### Step 5: Enable the Artisan Console Command

Add the command to your `app/Console/Kernel.php`:

```php
protected $commands = [
    //
    \InfusionWeb\Laravel\ContentCache\ContentCacheCommand::class,
];
```

This enables the console command which can be used to periodically cache the remote content in Laravel:

```bash
$ php artisan content:cache
```

The previous command will cache all configured content types, whereas the following command will cache only the "podcasts" type (assuming it is defined in `contentcache.php`).

```bash
$ php artisan content:cache podcasts
```

## Usage Example

Assuming that a content type "podcasts" is defined in the `config/contentcache.php` file:

```php
<?php

return [

    /*
     * Default configuration.
     */
    'default' => [
        // Default length of time (in minutes) to cache content.
        'minutes' => 60,
    ],

     /*
      * Configuration for custom content filters.
      */
    'podcasts' => [

        // Length of time (in minutes) to cache content.
        'minutes' => 60 * 3, // 3 hours

        // REST API endpoint for service from which to retrieve content.
        'endpoint' => 'https://podcasts.example.com/api/v1/content/podcasts',
        'query' => ['_format' => 'json'],

        // Perform data filter (value) on given field name (key). So in this
        // case, "id" and "episode" will be cast as integers, and "date_created"
        // and "date_changed" will be cast as Carbon date objects. All other
        // values will be cast as strings.
        'filters' => [
            'id' => 'int',
            'date_created' => 'date',
            'date_changed' => 'date',
            'episode' => 'int',
        ],

        // New fields to be created on cached content object from given field names.
        // E.g. Given an "episode" value of 13 and a "title" of "Lucky 13", the
        // new "slug" attribute (useful for use in routes) will have a value of
        // "13-lucky-13".
        'fields' => [
            'slug' => ['episode', 'title'],
        ],

        // Keys by which the cache should be indexed. I.e. each content
        // object will be cached under each of these index keys.
        'keys' => [
            'id',
            'slug',
            'uuid',
            'episode',
        ],
    ],
];
```

The following code can be used to cache and retrieve podcast content:

```php
<?php

use ContentCache;

// Manually cache remote podcast episodes, if this process is not handled via the
// Artisan console command.
ContentCache::profile('podcasts')->cache();

// Then retrieve a list of all episodes.
$episodes = ContentCache::profile('podcasts')->getAll();

// This could also be shortened to:
$episodes = ContentCache::profile('podcasts')->cache()->getAll();

// Individual content objects can be retrieved via configured indexes.
// In this case, by ID.
$episode = ContentCache::profile('podcasts')->getBy('id', 13);

// Or by slug (from a Laravel Request object), using the "magic function".
$cache = ContentCache::profile('podcasts');
$episode = $cache->getBySlug('13-lucky-13');

// This makes it easy to hand off to a Blade template.
return view('pages.podcast.episode', compact('episode'));
```

## Credits

- [Russell Keppner](https://github.com/rkeppner)
- [All Contributors](https://github.com/InfusionWeb/laravel-remote-content-cache/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
