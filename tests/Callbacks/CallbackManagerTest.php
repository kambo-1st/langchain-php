<?php

namespace Kambo\Langchain\Tests\Callbacks;

use PHPUnit\Framework\TestCase;
use Kambo\Langchain\Callbacks\CallbackManager;
use Kambo\Langchain\Callbacks\BaseCallbackHandler;
use Kambo\Langchain\LLMs\LLMResult;
use Throwable;

class CallbackManagerTest extends TestCase
{
    public function testAddHandler()
    {
        $manager = new CallbackManager();
        $handler = $this->createMock(BaseCallbackHandler::class);

        $manager->addHandler($handler);

        $this->assertContains($handler, $manager->handlers);
    }

    public function testRemoveHandler()
    {
        $handler = $this->createMock(BaseCallbackHandler::class);
        $manager = new CallbackManager([$handler]);

        $manager->removeHandler($handler);

        $this->assertNotContains($handler, $manager->handlers);
    }

    public function testSetHandlers()
    {
        $handler1 = $this->createMock(BaseCallbackHandler::class);
        $handler2 = $this->createMock(BaseCallbackHandler::class);
        $manager = new CallbackManager([$handler1]);

        $manager->setHandlers([$handler2]);

        $this->assertSame([$handler2], $manager->handlers);
    }

    public function testOnLLMStart()
    {
        $manager = new CallbackManager();
        $handler = $this->createMock(BaseCallbackHandler::class);

        $handler->expects($this->once())
            ->method('onLLMStart')
            ->with(
                $this->equalTo(['test_serialized']),
                $this->equalTo(['test_prompts']),
                $this->equalTo(['verbose' => true])
            );

        $manager->addHandler($handler);

        $manager->onLLMStart(['test_serialized'], ['test_prompts'], ['verbose' => true]);
    }

    public function testOnLLMNewToken()
    {
        $manager = new CallbackManager();
        $handler = $this->createMock(BaseCallbackHandler::class);

        $handler->expects($this->once())
            ->method('onLLMNewToken')
            ->with($this->equalTo('test_token'), $this->equalTo(['verbose' => true]));

        $manager->addHandler($handler);

        $manager->onLLMNewToken('test_token', ['verbose' => true]);
    }

    public function testOnLLMEnd()
    {
        $manager = new CallbackManager();
        $handler = $this->createMock(BaseCallbackHandler::class);
        $result = new LLMResult([], []);

        $handler->expects($this->once())
            ->method('onLLMEnd')
            ->with($this->equalTo($result), $this->equalTo(['verbose' => true]));

        $manager->addHandler($handler);

        $manager->onLLMEnd($result, ['verbose' => true]);
    }

    public function testOnLLMError()
    {
        $manager = new CallbackManager();
        $handler = $this->createMock(BaseCallbackHandler::class);
        $error = $this->createMock(Throwable::class);

        $handler->expects($this->once())
            ->method('onLLMError')
            ->with($this->equalTo($error), $this->equalTo(['verbose' => true]));

        $manager->addHandler($handler);

        $manager->onLLMError($error, ['verbose' => true]);
    }

    public function testOnChainStart()
    {
        $manager = new CallbackManager();
        $handler = $this->createMock(BaseCallbackHandler::class);

        $handler->expects($this->once())
            ->method('onChainStart')
            ->with($this->equalTo(['test_serialized']), $this->equalTo(['test_inputs']), $this->equalTo(['verbose' => true]));

        $manager->addHandler($handler);

        $manager->onChainStart(['test_serialized'], ['test_inputs'], ['verbose' => true]);
    }

    public function testOnChainEnd()
    {
        $manager = new CallbackManager();
        $handler = $this->createMock(BaseCallbackHandler::class);

        $handler->expects($this->once())
            ->method('onChainEnd')
            ->with($this->equalTo(['test_outputs']), $this->equalTo(['verbose' => true]));

        $manager->addHandler($handler);

        $manager->onChainEnd(['test_outputs'], ['verbose' => true]);
    }

    public function testOnChainError()
    {
        $manager = new CallbackManager();
        $handler = $this->createMock(BaseCallbackHandler::class);
        $error = $this->createMock(Throwable::class);

        $handler->expects($this->once())
            ->method('onChainError')
            ->with($this->equalTo($error), $this->equalTo(['verbose' => true]));

        $manager->addHandler($handler);

        $manager->onChainError($error, ['verbose' => true]);
    }

    public function testOnToolStart()
    {
        $manager = new CallbackManager();
        $handler = $this->createMock(BaseCallbackHandler::class);

        $handler->expects($this->once())
            ->method('onToolStart')
            ->with($this->equalTo(['test_serialized']), $this->equalTo('test_input_str'), $this->equalTo(['verbose' => true]));

        $manager->addHandler($handler);

        $manager->onToolStart(['test_serialized'], 'test_input_str', ['verbose' => true]);
    }

    public function testOnToolEnd()
    {
        $manager = new CallbackManager();
        $handler = $this->createMock(BaseCallbackHandler::class);

        $handler->expects($this->once())
            ->method('onToolEnd')
            ->with($this->equalTo('test_output'), ['verbose' => true]);

        $manager->addHandler($handler);

        $manager->onToolEnd('test_output', ['verbose' => true]);
    }

    public function testOnToolError()
    {
        $manager = new CallbackManager();
        $handler = $this->createMock(BaseCallbackHandler::class);
        $error = $this->createMock(Throwable::class);

        $handler->expects($this->once())
        ->method('onToolError')
        ->with($this->equalTo($error), ['verbose' => true]);

        $manager->addHandler($handler);

        $manager->onToolError($error, ['verbose' => true]);
    }

    public function testOnText()
    {
        $manager = new CallbackManager();
        $handler = $this->createMock(BaseCallbackHandler::class);

        $handler->expects($this->once())
        ->method('onText')
        ->with($this->equalTo('test_text'), ['verbose' => true]);

        $manager->addHandler($handler);

        $manager->onText('test_text', ['verbose' => true]);
    }
}
