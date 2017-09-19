<?php

namespace Northwoods\Broker;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
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
     * @var array
     */
    private $middleware = [];

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request, callable $default)
    {
        $handler = new CallableRequestHandler($default);

        return $this->process($request, $handler);
    }

    // MiddlewareInterface
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        $handler = new RequestHandler($this->middleware, $handler, $this->container);

        return $handler->handle($request);
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
            $this->middleware[] = [
                $condition,
                $mw
            ];
        }

        return $this;
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
