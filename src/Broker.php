<?php
declare(strict_types=1);

namespace Northwoods\Broker;

use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Broker implements Middleware
{
    /** @var Container */
    private $container;

    /** @var array */
    private $middleware = [];

    public function __construct(Container $container = null)
    {
        $this->container = $container;
    }

    public function handle(Request $request, callable $default): Response
    {
        $handler = new CallableRequestHandler($default);

        return $this->process($request, $handler);
    }

    public function process(Request $request, Handler $handler): Response
    {
        $handler = new RequestHandler($this->middleware, $handler, $this->container);

        return $handler->handle($request);
    }

    /**
     * @param array|string|Middleware $middleware
     */
    public function always($middleware): Broker
    {
        return $this->when($this->alwaysTrue(), $middleware);
    }

    /**
     * @param array|string|Middleware $middleware
     */
    public function when(callable $condition, $middleware): Broker
    {
        if (!is_array($middleware)) {
            $middleware = [$middleware];
        }

        foreach ($middleware as $mw) {
            $this->middleware[] = [$condition, $mw];
        }

        return $this;
    }

    private function alwaysTrue(): callable
    {
        return static function () {
            return true;
        };
    }
}
