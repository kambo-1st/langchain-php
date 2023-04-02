<?php

namespace Kambo\Langchain\Tests\Callbacks;

use PHPUnit\Framework\TestCase;
use Kambo\Langchain\Callbacks\StdOutCallbackHandler;

use function ob_start;
use function ob_get_clean;

class StdOutCallbackHandlerTest extends TestCase
{
    public function testOnChainStart()
    {
        $handler = new StdOutCallbackHandler();

        ob_start();
        $handler->onChainStart(['name' => 'TestChain'], []);
        $output = ob_get_clean();

        $this->assertEquals("\n\n\033[1m> Entering new TestChain chain...\033[0m\n", $output);
    }

    public function testOnChainEnd()
    {
        $handler = new StdOutCallbackHandler();

        ob_start();
        $handler->onChainEnd([]);
        $output = ob_get_clean();

        $this->assertEquals("\n\033[1m> Finished chain.\033[0m\n", $output);
    }

    public function testOnText()
    {
        $handler = new StdOutCallbackHandler('32');

        ob_start();
        $handler->onText('Hello, World!');
        $output = ob_get_clean();

        $this->assertEquals("\033[32mHello, World!\033[0m", $output);
    }
}
