<?php

namespace Northwoods\Broker;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use SplObjectStorage;

class Delegate implements DelegateInterface
{
    /**
     * @var SplObjectStorage
     */
    private $middleware;

    /**
     * @var DelegateInterface $nextDelegate
     */
    private $nextDelegate;

    public function __construct(SplObjectStorage $middleware, DelegateInterface $nextDelegate)
    {
        $this->middleware = clone $middleware;
        $this->nextDelegate = $nextDelegate;

        // Rewind the middleware to the start
        $this->middleware->rewind();
    }

    // DelegateInterface
    public function process(ServerRequestInterface $request)
    {
        /** @var MiddlewareInterface */
        $middleware = $this->middleware->current();

        if (empty($middleware)) {
            return $this->nextDelegate->process($request);
        }

        /** @var callable */
        $condition = $this->middleware[$middleware];

        /** @var DelegateInterface */
        $delegate = $this->nextDelegate();

        if ($condition($request)) {
            return $middleware->process($request, $delegate);
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
        $copy->middleware->next();

        return $copy;
    }
}
