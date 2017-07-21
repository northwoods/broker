<?php

namespace Northwoods\Broker;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use SplObjectStorage;

class Broker implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var SplObjectStorage
     */
    private $middleware;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->middleware = new SplObjectStorage();
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request, callable $default)
    {
        $delegate = new CallableDelegate($default);

        return $this->process($request, $delegate);
    }

    // MiddlewareInterface
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $delegate = new Delegate($this->middleware, $delegate);

        return $delegate->process($request);
    }

    /**
     * @param array|string|MiddlewareInterface $middleware
     * @return self
     */
    public function always($middleware)
    {
        return $this->when($this->alwaysTrue(), $middleware);
    }

    /**
     * @param array|string|MiddlewareInterface $middleware
     * @return self
     */
    public function when(callable $condition, $middleware)
    {
        if (!is_array($middleware)) {
            $middleware = [$middleware];
        }

        foreach ($middleware as $mw) {
            $this->addConditionalMiddleware($condition, $this->resolveMiddleware($mw));
        }

        return $this;
    }

    /**
     * @return void
     */
    private function addConditionalMiddleware(callable $condition, MiddlewareInterface $middleware)
    {
        $this->middleware[$middleware] = $condition;
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

    /**
     * @return callable
     */
    private function alwaysTrue()
    {
        return static function () {
            return true;
        };
    }
}
