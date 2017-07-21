<?php

namespace Northwoods\Broker;

use Eloquent\Phony\Phpunit\Phony;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class BrokerContainerTest extends TestCase
{
    use CanMock;

    /**
     * @var \Eloquent\Phony\Mock\Handle\InstanceHandle
     */
    private $container;

    /**
     * @var Broker
     */
    private $broker;

    public function setUp()
    {
        $this->container = Phony::mock(ContainerInterface::class);

        $this->broker = new Broker($this->container->get());
    }

    public function testResolve()
    {
        // Mock
        $middleware = $this->mockMiddleware();
        $middlewareClass = $middleware->className();

        $this->container->get->with($middlewareClass)->returns($middleware);

        // Execute
        $this->broker->always($middlewareClass);

        // Verify
        $this->assertResponse($this->process());

        Phony::inOrder(...[
            $this->container->get->called(),
            $middleware->process->called(),
        ]);
    }
}
