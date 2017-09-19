<?php

namespace Northwoods\Broker;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use SplObjectStorage;

class RequestHandler implements RequestHandlerInterface
{
    /**
     * @var array
     */
    private $middleware;

    /**
     * @var RequestHandlerInterface $nextRequestHandler
     */
    private $nextRequestHandler;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var int
     */
    private $index = 0;

    public function __construct(array $middleware, RequestHandlerInterface $nextRequestHandler, ContainerInterface $container = null)
    {
        $this->middleware = $middleware;
        $this->nextRequestHandler = $nextRequestHandler;
        $this->container = $container;
    }

    // RequestHandlerInterface
    public function handle(ServerRequestInterface $request)
    {
        if (empty($this->middleware[$this->index])) {
            return $this->nextRequestHandler->handle($request);
        }

        /** @var callable */
        $condition = $this->middleware[$this->index][0];

        /** @var MiddlewareInterface|string */
        $middleware = $this->middleware[$this->index][1];

        /** @var RequestHandlerInterface */
        $handler = $this->nextRequestHandler();

        if ($condition($request)) {
            return $this->resolveMiddleware($middleware)->process($request, $handler);
        } else {
            return $handler->handle($request);
        }
    }

    /**
     * @return RequestHandlerInterface
     */
    private function nextRequestHandler()
    {
        $copy = clone $this;
        $copy->index++;

        return $copy;
    }

    /**
     * @param string|MiddlewareInterface $middleware
     * @return MiddlewareInterface
     */
    private function resolveMiddleware($middleware)
    {
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        return $this->container->get($middleware);
    }
}
