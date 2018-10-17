<?php
declare(strict_types=1);

namespace Northwoods\Broker;

use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Phpunit\Phony;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BrokerTest extends TestCase
{
    /** @var Broker */
    private $broker;

    public function setUp(): void
    {
        $this->broker = new Broker();
    }

    public function testCanAppendMiddleware(): void
    {
        // Mock
        $mw1 = $this->mockMiddleware();
        $mw2 = $this->mockMiddleware();

        $this->broker->append($mw1->get(), $mw2->get());

        // Execute
        $response = $this->broker->process(
            $this->mockRequest()->get(),
            $this->mockHandler()->get()
        );

        // Verify
        Phony::inOrder(
            $mw1->process->once()->called(),
            $mw2->process->once()->called()
        );
    }

    public function testCanPrependMiddleware(): void
    {
        // Mock
        $mw1 = $this->mockMiddleware();
        $mw2 = $this->mockMiddleware();

        $this->broker->append($mw1->get());
        $this->broker->prepend($mw2->get());

        // Execute
        $response = $this->broker->process(
            $this->mockRequest()->get(),
            $this->mockHandler()->get()
        );

        // Verify
        Phony::inOrder(
            $mw2->process->once()->called(),
            $mw1->process->once()->called()
        );
    }

    public function testCannotHandleWithoutMiddleware(): void
    {
        // Expect
        $this->expectException(OutOfBoundsException::class);

        // Execute
        $this->broker->handle(
            $this->mockRequest()->get()
        );
    }

    private function mockHandler(): InstanceHandle
    {
        $handler = Phony::mock(RequestHandlerInterface::class);
        $handler->handle->returns($this->mockResponse());

        return $handler;
    }

    private function mockMiddleware(): InstanceHandle
    {
        $middleware = Phony::mock(MiddlewareInterface::class);

        $middleware->process->does(function ($request, $handler) {
            return $handler->handle($request);
        });

        return $middleware;
    }

    private function mockRequest(): InstanceHandle
    {
        return Phony::mock(ServerRequestInterface::class);
    }

    private function mockResponse(): InstanceHandle
    {
        return Phony::mock(ResponseInterface::class);
    }
}
