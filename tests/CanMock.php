<?php

namespace Northwoods\Broker;

use Eloquent\Phony\Phpunit\Phony;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

trait CanMock
{
    /**
     * @return void
     */
    private function assertResponse($value)
    {
        $this->assertInstanceOf(ResponseInterface::class, $value);
    }

    /**
     * @return ResponseInterface
     */
    private function handle(callable $default)
    {
        if (empty($request)) {
            $request = $this->mockRequest()->get();
        }

        return $this->broker->handle($request, $default);
    }

    /**
     * @return ResponseInterface
     */
    private function process(ServerRequestInterface $request = null, DelegateInterface $delegate = null)
    {
        if (empty($request)) {
            $request = $this->mockRequest()->get();
        }

        if (empty($delegate)) {
            $delegate = $this->mockDelegate()->get();
        }

        return $this->broker->process($request, $delegate);
    }

    /**
     * @return \Eloquent\Phony\Mock\Handle\InstanceHandle
     */
    private function mockRequest()
    {
        return Phony::mock(ServerRequestInterface::class);
    }

    /**
     * @return \Eloquent\Phony\Mock\Handle\InstanceHandle
     */
    private function mockResponse()
    {
        return Phony::mock(ResponseInterface::class);
    }

    /**
     * @return \Eloquent\Phony\Mock\Handle\InstanceHandle
     */
    private function mockMiddleware()
    {
        $middleware = Phony::mock(MiddlewareInterface::class);

        $middleware->process->does(static function ($request, $delegate) {
            return $delegate->process($request);
        });

        return $middleware;
    }

    /**
     * @return \Eloquent\Phony\Mock\Handle\InstanceHandle
     */
    private function mockDelegate($response = null)
    {
        if (empty($response)) {
            $response = $this->mockResponse();
        }

        $delegate = Phony::mock(DelegateInterface::class);

        $delegate->process->returns($response);

        return $delegate;
    }
}
