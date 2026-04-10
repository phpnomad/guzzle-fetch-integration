# phpnomad/guzzle-fetch

[![Latest Version](https://img.shields.io/packagist/v/phpnomad/guzzle-fetch.svg)](https://packagist.org/packages/phpnomad/guzzle-fetch)
[![Total Downloads](https://img.shields.io/packagist/dt/phpnomad/guzzle-fetch.svg)](https://packagist.org/packages/phpnomad/guzzle-fetch)
[![PHP Version](https://img.shields.io/packagist/php-v/phpnomad/guzzle-fetch.svg)](https://packagist.org/packages/phpnomad/guzzle-fetch)
[![License](https://img.shields.io/packagist/l/phpnomad/guzzle-fetch.svg)](https://packagist.org/packages/phpnomad/guzzle-fetch)

Integrates `guzzlehttp/guzzle` with PHPNomad's `phpnomad/fetch` abstraction. The package ships a concrete `FetchStrategy` implementation backed by a `GuzzleHttp\Client`, so any application code that constructor-injects `PHPNomad\Fetch\Interfaces\FetchStrategy` can make outbound HTTP requests through Guzzle without touching Guzzle directly.

This is the transport you want in non-WordPress runtimes where `wp_remote_request` is not available: CLI tools, SaaS backends, worker processes, and any PHPNomad application that prefers Guzzle to the WordPress HTTP API.

## Installation

```bash
composer require phpnomad/guzzle-fetch
```

## What This Provides

- `PHPNomad\Guzzle\FetchIntegration\Strategies\FetchStrategy` implements `PHPNomad\Fetch\Interfaces\FetchStrategy`. It holds a `GuzzleHttp\Client`, reads the URL, method, headers, body, and query params off the incoming `FetchPayload`, and forwards them to `Client::request()`. Guzzle exceptions are converted to `PHPNomad\Rest\Exceptions\RestException` with the upstream status code preserved when available.
- `PHPNomad\Guzzle\FetchIntegration\Models\Response` implements `PHPNomad\Http\Interfaces\Response`. The strategy populates it from the Guzzle response and returns it to callers, so downstream code sees the same interface regardless of the underlying transport.
- `PHPNomad\Guzzle\FetchIntegration\Initializer` is the loader hook that binds the strategy to the `FetchStrategy` interface in the DI container.

## Requirements

- `phpnomad/fetch`, the HTTP abstraction this package implements.
- `guzzlehttp/guzzle`, the HTTP client this package bridges.

The package also pulls in `phpnomad/http`, `phpnomad/rest`, `phpnomad/di`, and `phpnomad/loader` as part of the standard PHPNomad integration surface.

## Usage

Registration goes through `phpnomad/loader`. Add `new Initializer()` to the list of initializers your application passes to the `Bootstrapper`, and the container will bind the Guzzle-backed strategy to `PHPNomad\Fetch\Interfaces\FetchStrategy` during boot.

```php
<?php

namespace MyApp\Bootstrap;

use PHPNomad\Core\Bootstrap\CoreInitializer;
use PHPNomad\Di\Container\Container;
use PHPNomad\Guzzle\FetchIntegration\Initializer as GuzzleFetchInitializer;
use PHPNomad\Loader\Bootstrapper;

class Application
{
    protected Container $container;

    public function boot(): void
    {
        $this->container = new Container();

        $bootstrapper = new Bootstrapper(
            $this->container,
            new CoreInitializer(),
            new GuzzleFetchInitializer(),
            // ...your application initializers
        );

        $bootstrapper->load();
    }
}
```

Once the bootstrapper has run, any service that declares `FetchStrategy` as a constructor argument receives the Guzzle-backed implementation. No further configuration is required for the default case. If you need custom Guzzle middleware, a base URI, or a preconfigured client, subclass the strategy and bind your subclass to the interface in your own initializer.

## Documentation

Full PHPNomad documentation lives at [phpnomad.com](https://phpnomad.com). Guzzle's own documentation lives at [docs.guzzlephp.org](https://docs.guzzlephp.org).

## License

Released under the [MIT License](LICENSE).
