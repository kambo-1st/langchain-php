<?php

namespace Kambo\Langchain\Callbacks;

use Kambo\Langchain\LLMs\LLMResult;
use Throwable;

use function array_search;
use function array_splice;

class CallbackManager extends BaseCallbackManager
{
    /** @var array<BaseCallbackHandler> */
    public array $handlers;

    public function __construct(array $handlers = [])
    {
        $this->handlers = $handlers;
    }

    public function onLLMStart(array $serialized, array $prompts, array $additionalArguments = [])
    {
        $verbose = $additionalArguments['verbose'] ?? false;
        foreach ($this->handlers as $handler) {
            if (! $handler->ignoreLLM && ($verbose || $handler->alwaysVerbose)) {
                $handler->onLLMStart($serialized, $prompts, $additionalArguments);
            }
        }
    }

    public function onLLMNewToken(string $token, array $additionalArguments = [])
    {
        $verbose = $additionalArguments['verbose'] ?? false;
        foreach ($this->handlers as $handler) {
            if (! $handler->ignoreLLM && ($verbose || $handler->alwaysVerbose)) {
                $handler->onLLMNewToken($token, $additionalArguments);
            }
        }
    }

    public function onLLMEnd(LLMResult $response, array $additionalArguments = [])
    {
        $verbose = $additionalArguments['verbose'] ?? false;
        foreach ($this->handlers as $handler) {
            if (! $handler->ignoreLLM && ($verbose || $handler->alwaysVerbose)) {
                $handler->onLLMEnd($response, $additionalArguments);
            }
        }
    }

    public function onLLMError(Throwable $error, array $additionalArguments = [])
    {
        $verbose = $additionalArguments['verbose'] ?? false;
        foreach ($this->handlers as $handler) {
            if (! $handler->ignoreLLM && ($verbose || $handler->alwaysVerbose)) {
                $handler->onLLMError($error, $additionalArguments);
            }
        }
    }

    public function onChainStart(array $serialized, array $inputs, array $additionalArguments = [])
    {
        $verbose = $additionalArguments['verbose'] ?? false;
        foreach ($this->handlers as $handler) {
            if (! $handler->ignoreChain && ($verbose || $handler->alwaysVerbose)) {
                $handler->onChainStart($serialized, $inputs, $additionalArguments);
            }
        }
    }

    public function onChainEnd(array $outputs, array $additionalArguments = [])
    {
        $verbose = $additionalArguments['verbose'] ?? false;
        foreach ($this->handlers as $handler) {
            if (! $handler->ignoreChain && ($verbose || $handler->alwaysVerbose)) {
                $handler->onChainEnd($outputs, $additionalArguments);
            }
        }
    }

    public function onChainError(Throwable $error, array $additionalArguments = [])
    {
        $verbose = $additionalArguments['verbose'] ?? false;
        foreach ($this->handlers as $handler) {
            if (! $handler->ignoreChain && ($verbose || $handler->alwaysVerbose)) {
                $handler->onChainError($error, $additionalArguments);
            }
        }
    }

    public function onToolStart(array $serialized, string $inputStr, array $additionalArguments = []): void
    {
        $verbose = $additionalArguments['verbose'] ?? false;
        /** Run when tool starts running. */
        foreach ($this->handlers as $handler) {
            if (! $handler->ignoreAgent) {
                if ($verbose || $handler->alwaysVerbose) {
                    $handler->onToolStart($serialized, $inputStr, $additionalArguments);
                }
            }
        }
    }

    public function onToolEnd(string $output, array $additionalArguments = []): void
    {
        $verbose = $additionalArguments['verbose'] ?? false;
        /** Run when tool ends running. */
        foreach ($this->handlers as $handler) {
            if (! $handler->ignoreAgent) {
                if ($verbose || $handler->alwaysVerbose) {
                    $handler->onToolEnd($output, $additionalArguments);
                }
            }
        }
    }

    public function onToolError($error, array $additionalArguments = []): void
    {
        $verbose = $additionalArguments['verbose'] ?? false;
        /** Run when tool errors. */
        foreach ($this->handlers as $handler) {
            if (! $handler->ignoreAgent) {
                if ($verbose || $handler->alwaysVerbose) {
                    $handler->onToolError($error, $additionalArguments);
                }
            }
        }
    }

    public function onText(string $text, array $additionalArguments = []): void
    {
        $verbose = $additionalArguments['verbose'] ?? false;
        /** Run on additional input from chains and agents. */
        foreach ($this->handlers as $handler) {
            if ($verbose || $handler->alwaysVerbose) {
                $handler->onText($text, $additionalArguments);
            }
        }
    }

    public function addHandler(BaseCallbackHandler $callback): void
    {
        /** Add a handler to the callback manager. */
        $this->handlers[] = $callback;
    }

    public function removeHandler(BaseCallbackHandler $callback): void
    {
        /** Remove a handler from the callback manager. */
        $index = array_search($callback, $this->handlers, true);
        if ($index !== false) {
            array_splice($this->handlers, $index, 1);
        }
    }

    public function setHandlers(array $handlers): void
    {
        /** Set handlers as the only handlers on the callback manager. */
        $this->handlers = $handlers;
    }
}
