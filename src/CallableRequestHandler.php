<?php

namespace Northwoods\Broker;

use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

class CallableRequestHandler implements RequestHandlerInterface
{
    /**
     * @var callable
     */
    private $handler;

    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    // RequestHandlerInterface
    public function handle(ServerRequestInterface $request)
    {
        return call_user_func($this->handler, $request);
    }
}
