<?php

namespace Northwoods\Broker;

use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CallableRequestHandler implements Handler
{
    /** @var callable */
    private $handler;

    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    public function handle(Request $request): Response
    {
        return call_user_func($this->handler, $request);
    }
}
