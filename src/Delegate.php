<?php

namespace Northwoods\Broker;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use SplObjectStorage;

class Delegate implements DelegateInterface
{
    /**
     * @var array
     */
    private $middleware;

    /**
     * @var DelegateInterface $nextDelegate
     */
    private $nextDelegate;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var int
     */
    private $index = 0;

    public function __construct(array $middleware, DelegateInterface $nextDelegate, ContainerInterface $container = null)
    {
        $this->middleware = $middleware;
        $this->nextDelegate = $nextDelegate;
        $this->container = $container;
    }

    // DelegateInterface
    public function process(ServerRequestInterface $request)
    {
        if (empty($this->middleware[$this->index])) {
            return $this->nextDelegate->process($request);
        }

        /** @var callable */
        $condition = $this->middleware[$this->index][0];

        /** @var MiddlewareInterface|string */
        $middleware = $this->middleware[$this->index][1];

        /** @var DelegateInterface */
        $delegate = $this->nextDelegate();

        if ($condition($request)) {
            return $this->resolveMiddleware($middleware)->process($request, $delegate);
        } else {
            return $delegate->process($request);
        }
    }

    /**
     * @return DelegateInterface
     */
    private function nextDelegate()
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
