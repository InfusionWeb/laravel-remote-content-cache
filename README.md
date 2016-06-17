# Laravel 5 Remote Content Cache
[![Latest Stable Version](https://poser.pugx.org/infusionweb/laravel-remote-content-cache/v/stable)](https://packagist.org/packages/infusionweb/laravel-remote-content-cache) [![Total Downloads](https://poser.pugx.org/infusionweb/laravel-remote-content-cache/downloads)](https://packagist.org/packages/infusionweb/laravel-remote-content-cache) [![Latest Unstable Version](https://poser.pugx.org/infusionweb/laravel-remote-content-cache/v/unstable)](https://packagist.org/packages/infusionweb/laravel-remote-content-cache) [![License](https://poser.pugx.org/infusionweb/laravel-remote-content-cache/license)](https://packagist.org/packages/infusionweb/laravel-remote-content-cache)

## A Laravel 5 caching framework for retrieving and caching content from remote server.

This package provides a Laravel 5 service provider and facade for [dchesterton/marketo-rest-api](https://github.com/dchesterton/marketo-rest-api), which is a Composer package that serves as an "unofficial PHP client for the Marketo.com REST API."

When enabled and configured, this package allows a more convenient use of the *Marketo REST API Client* functionality, through a Laravel facade, as well as adding some configuration options for added ease of use.

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
        "infusionweb/laravel-remote-content-cache": "~0.1.0"
    }
}
```

### Step 2: Register the Service Provider

Add the service provider to your `config/app.php`:

```php
'providers' => [
    //
    InfusionWeb\Laravel\ContentCache\ContentCacheServiceProvider::class,
];
```

### Step 3: Enable the Facade

Add the facade to your `config/app.php`:

```php
'aliases' => [
    //
    'ContentCache' => InfusionWeb\Laravel\ContentCache\ContentCacheFacade::class,
];
```

### Step 4: Publish the package config file

```bash
$ php artisan vendor:publish --provider="InfusionWeb\Laravel\ContentCache\ContentCacheServiceProvider"
```

You may now setup Marketo authentication and other preferences by editing the `config/contentcache.php` file.

### Step 5: Enable the Artisan Console Command

Add the command to your `app/Console/Kernel.php`:

```php
protected $commands = [
    //
    \InfusionWeb\Laravel\ContentCache\ContentCacheCommand::class,
];
```

## Usage Example

```php
<?php

use Marketo;

$fields = [
    'email' => 'email@example.com',
    'firstName' => 'Example',
    'lastName' => 'User',
];

try {
    $result = Marketo::createOrUpdateLead($fields);
}
catch(\InfusionWeb\Laravel\Marketo\Exceptions\MarketoClientException $e) {
    die('We couldn’t save your information!');
}
```

## Credits

- [Russell Keppner](https://github.com/rkeppner)
- [All Contributors](https://github.com/InfusionWeb/laravel-remote-content-cache/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
