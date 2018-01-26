<?php

namespace Northwoods\Broker;

use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RequestHandler implements Handler
{
    /** @var array */
    private $middleware;

    /** @var Handler */
    private $nextRequestHandler;

    /** @var Container */
    private $container;

    /** @var int */
    private $index = 0;

    public function __construct(array $middleware, Handler $nextRequestHandler, Container $container = null)
    {
        $this->middleware = $middleware;
        $this->nextRequestHandler = $nextRequestHandler;
        $this->container = $container;
    }

    public function handle(Request $request): Response
    {
        if (empty($this->middleware[$this->index])) {
            return $this->nextRequestHandler->handle($request);
        }

        /** @var callable */
        $condition = $this->middleware[$this->index][0];

        /** @var Middleware |string */
        $middleware = $this->middleware[$this->index][1];

        /** @var Handler */
        $handler = $this->nextRequestHandler();

        if ($condition($request)) {
            return $this->resolveMiddleware($middleware)->process($request, $handler);
        } else {
            return $handler->handle($request);
        }
    }

    private function nextRequestHandler(): Handler
    {
        $copy = clone $this;
        $copy->index++;

        return $copy;
    }

    /**
     * @param string|Middleware $middleware
     */
    private function resolveMiddleware($middleware): Middleware
    {
        if ($middleware instanceof Middleware) {
            return $middleware;
        }

        return $this->container->get($middleware);
    }
}
