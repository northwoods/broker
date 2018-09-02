Northwoods Broker
=================

[![Become a Supporter](https://img.shields.io/badge/patreon-sponsor%20me-e6461a.svg)](https://www.patreon.com/shadowhand)
[![Latest Stable Version](https://img.shields.io/packagist/v/northwoods/broker.svg)](https://packagist.org/packages/northwoods/broker)
[![License](https://img.shields.io/packagist/l/northwoods/broker.svg)](https://github.com/northwoods/broker/blob/master/LICENSE)
[![Build Status](https://travis-ci.org/northwoods/broker.svg)](https://travis-ci.org/northwoods/broker)
[![Code Coverage](https://scrutinizer-ci.com/g/northwoods/broker/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/northwoods/broker/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/northwoods/broker/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/northwoods/broker/?branch=master)

Broker is a [PSR-15][psr-15] middleware dispatcher that uses conditional matching.
As opposed to most middleware dispatcher, Broker verifies each middleware
can be applied to the request before executing it. This allows for a centralized
configuration of middleware without compromising flexibility.

Attempts to be [PSR-1][psr-1], [PSR-2][psr-2], [PSR-4][psr-4], and [PSR-15][psr-15] compliant.

[psr-1]: http://www.php-fig.org/psr/psr-1/
[psr-2]: http://www.php-fig.org/psr/psr-2/
[psr-4]: http://www.php-fig.org/psr/psr-4/
[psr-11]: http://www.php-fig.org/psr/psr-11/
[psr-15]: http://www.php-fig.org/psr/psr-15/

## Install

```
composer require northwoods/broker
```

## Usage

Broker works best when combined with a [PSR-11 container][psr-11].
When a container is provided, middleware can be added by service key which will be resolved to a service at the time when that middlewre is processed.
Without the container middleware must be fully constructed.

```php
use Northwoods\Broker\Broker;

$broker = new Broker($container);
```

Broker operates on conditions. The middleware will not be executed unless the
condition returns `true`. Because some middleware is not conditional the `always()`
method makes it easy to add middleware that must be executed on every request.
The `when()` method accepts a condition and a list of middleware to execute if
the condition passes. All middleware is executed in the order added.

```php
// Always execute these middleware
$broker->always([
    Acme\HandleErrors::class,
    Acme\DetectClientIp::class,
]);

// Do we need to redirect to a secure connection?
$broker->when(
    function (ServerRequestInterface $request) {
        $scheme = $request->getUri()->getScheme();
        return $scheme !== 'https';
    },
    Acme\RedirectHttps::class,
);

// Add more middleware to be executed
$broker->always([
    Acme\ParseBody::class,
    Acme\HandleRequest::class,
]);
```

To dispatch the middleware call the `handle()` method with a request instance
and a callable `$default` that returns the response, typically a `404 Not Found`,
if no middleware can handle the request.

```php
// Define the callable to create a 404 response
$default = [Acme\ResponseFactory::class, 'notFound'];

// Execute the middleware as a dispatcher
$response = $broker->handle($request, $default);
```

Broker also implements `MiddlewareInterface` and can be used as a middleware:

```
// Execute the broker like any other PSR-15 middleware
$response = $broker->process($request, $handler);
```

## License

MIT
