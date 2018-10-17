Northwoods Broker
=================

[![Become a Supporter](https://img.shields.io/badge/patreon-sponsor%20me-e6461a.svg)](https://www.patreon.com/shadowhand)
[![Latest Stable Version](https://img.shields.io/packagist/v/northwoods/broker.svg)](https://packagist.org/packages/northwoods/broker)
[![License](https://img.shields.io/packagist/l/northwoods/broker.svg)](https://github.com/northwoods/broker/blob/master/LICENSE)
[![Build Status](https://travis-ci.org/northwoods/broker.svg)](https://travis-ci.org/northwoods/broker)
[![Code Coverage](https://scrutinizer-ci.com/g/northwoods/broker/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/northwoods/broker/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/northwoods/broker/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/northwoods/broker/?branch=master)

Broker is a dead simple [PSR-15][psr-15] middleware dispatcher. Broker implements
both `RequestHandlerInterface` and `MiddlewareInterface` for maximum flexibility.

[psr-15]: http://www.php-fig.org/psr/psr-15/

## Install

```
composer require northwoods/broker
```

## Usage

```php
use Acme\Middleware;
use Northwoods\Broker\Broker;

/** @var \Psr\Http\Message\ServerRequestInterface */
$request = /* any server request */;

// Use append() or prepend() to add middleware
$broker = new Broker();
$broker->append(new Middleware\ParseRequest());
$broker->prepend(new Middleware\CheckIp());

/** @var \Psr\Http\Message\ResponseInterface */
$response = $broker->handle($request);
```

### `append(...$middleware)`

Add one or more middleware to the end of the stack.

### `prepend(...$middleware)`

Add one or more middleware to be beginning of the stack.

### `handle($request)`

Dispatch the middleware stack as a request handler. If the end of the stack is
reached and no response has been generated, an `OutOfBoundsException` will
be thrown.

### `process($request, $handler)`

Dispatch the middleware stack as a middleware. If the end of the stack is reached
and no response has been generated, the `$handler` will be called.

## Suggested Packages

- Conditional middleware execution can be provided by [northwoods/conditional-middleware](https://github.com/northwoods/conditional-middleware)
- Lazy middleware instantiation can be provided by [northwoods/lazy-middleware](https://github.com/northwoods/lazy-middleware)
- Response sending can be provided by [http-interop/response-sender](https://github.com/http-interop/response-sender)
