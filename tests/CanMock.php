<?php

namespace Northwoods\Broker;

use Eloquent\Phony\Phpunit\Phony;
use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
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
    private function process(ServerRequestInterface $request = null, RequestHandlerInterface $handler = null)
    {
        if (empty($request)) {
            $request = $this->mockRequest()->get();
        }

        if (empty($handler)) {
            $handler = $this->mockRequestHandler()->get();
        }

        return $this->broker->process($request, $handler);
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

        $middleware->process->does(static function ($request, $handler) {
            return $handler->handle($request);
        });

        return $middleware;
    }

    /**
     * @return \Eloquent\Phony\Mock\Handle\InstanceHandle
     */
    private function mockRequestHandler($response = null)
    {
        if (empty($response)) {
            $response = $this->mockResponse();
        }

        $handler = Phony::mock(RequestHandlerInterface::class);

        $handler->handle->returns($response);

        return $handler;
    }
}
