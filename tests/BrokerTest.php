<?php

namespace Northwoods\Broker;

use Eloquent\Phony\Phpunit\Phony;
use PHPUnit\Framework\TestCase;

class BrokerTest extends TestCase
{
    use CanMock;

    /**
     * @var Broker
     */
    private $broker;

    public function setUp()
    {
        $this->broker = new Broker();
    }

    public function testAlways()
    {
        // Mock
        $mw1 = $this->mockMiddleware();
        $mw2 = $this->mockMiddleware();

        // Execute
        $this->broker->always([
            $mw1->get(),
            $mw2->get(),
        ]);

        // Verify
        $this->assertResponse($this->process());

        Phony::inOrder(...[
            $mw1->process->called(),
            $mw2->process->called(),
        ]);
    }

    public function testWhen()
    {
        // Mock
        $mw1 = $this->mockMiddleware();
        $mw2 = $this->mockMiddleware();
        $mw3 = $this->mockMiddleware();

        // Execute
        $never = static function () {
            return false;
        };

        $this->broker->always($mw1->get());
        $this->broker->when($never, $mw2->get());
        $this->broker->always($mw3->get());

        // Verify
        $this->assertResponse($this->process());

        $mw2->noInteraction();

        Phony::inOrder(...[
            $mw1->process->called(),
            $mw3->process->called(),
        ]);
    }

    public function testHandle()
    {
        // Mock
        $default = Phony::stub();
        $default->returns($this->mockResponse());

        // Verify
        $this->assertResponse($this->handle($default));

        Phony::inOrder(...[
            $default->called(),
        ]);
    }
}
