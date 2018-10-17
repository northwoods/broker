<?php
declare(strict_types=1);

namespace Northwoods\Broker;

use OutOfBoundsException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Broker implements
    MiddlewareInterface,
    RequestHandlerInterface
{
    /** @var MiddlewareInterface[] */
    private $middleware = [];

    /**
     * Add middleware to the end of the stack
     */
    public function append(MiddlewareInterface ...$middleware): self
    {
        array_push($this->middleware, ...$middleware);

        return $this;
    }

    /**
     * Add middleware to the beginning of the stack
     */
    public function prepend(MiddlewareInterface ...$middleware): self
    {
        array_unshift($this->middleware, ...$middleware);

        return $this;
    }

    // RequestHandlerInterface
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $broker = clone $this;

        return $broker->nextMiddleware()->process($request, $broker);
    }

    // MiddlewareInterface
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $this->handle($request);
        } catch (OutOfBoundsException $e) {
            return $handler->handle($request);
        }
    }

    /**
     * @throws OutOfBoundsException If no middleware is available
     */
    private function nextMiddleware(): MiddlewareInterface
    {
        $middleware = array_shift($this->middleware);

        if ($middleware === null) {
            throw new OutOfBoundsException("End of middleware stack");
        }

        return $middleware;
    }
}
