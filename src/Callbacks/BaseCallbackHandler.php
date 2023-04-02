<?php

namespace Kambo\Langchain\Callbacks;

use Kambo\Langchain\LLMs\LLMResult;
use Exception;

/**
 * Base callback handler that can be used to handle callbacks from langchain.
 */
abstract class BaseCallbackHandler
{
    public bool $ignoreLLM = false;
    public bool $ignoreChain = false;
    public bool $alwaysVerbose = false;
    public bool $ignoreAgent = false;

    /**
     * @return bool
     */
    public function isIgnoreAgent(): bool
    {
        return $this->ignoreAgent;
    }

    /**
     * @param bool $ignoreAgent
     */
    public function setIgnoreAgent(bool $ignoreAgent): void
    {
        $this->ignoreAgent = $ignoreAgent;
    }

    /**
     * @return bool
     */
    public function isIgnoreLLM(): bool
    {
        return $this->ignoreLLM;
    }

    /**
     * @param bool $ignoreLLM
     */
    public function setIgnoreLLM(bool $ignoreLLM): void
    {
        $this->ignoreLLM = $ignoreLLM;
    }

    /**
     * @return bool
     */
    public function isIgnoreChain(): bool
    {
        return $this->ignoreChain;
    }

    /**
     * @param bool $ignoreChain
     */
    public function setIgnoreChain(bool $ignoreChain): void
    {
        $this->ignoreChain = $ignoreChain;
    }

    /**
     * @return bool
     */
    public function isAlwaysVerbose(): bool
    {
        return $this->alwaysVerbose;
    }

    /**
     * @param bool $alwaysVerbose
     */
    public function setAlwaysVerbose(bool $alwaysVerbose): void
    {
        $this->alwaysVerbose = $alwaysVerbose;
    }

    /**
     * Run when LLM starts running.
     *
     * @param array $serialized
     * @param array $prompts
     * @param array $additionalArguments
     *
     * @return mixed
     */
    abstract public function onLLMStart(array $serialized, array $prompts, array $additionalArguments = []);

    /**
     * Run on new LLM token. Only available when streaming is enabled.
     *
     * @param string $token
     * @param array $additionalArguments
     *
     * @return mixed
     */
    abstract public function onLLMNewToken(string $token, array $additionalArguments = []);

    /**
     * Run when LLM ends running.
     *
     * @param LLMResult $response
     * @param array     $additionalArguments
     *
     * @return mixed
     */
    abstract public function onLLMEnd(LLMResult $response, array $additionalArguments = []);

    /**
     * Run when LLM errors
     *
     * @param Exception $error
     * @param array $additionalArguments
     *
     * @return mixed
     */
    abstract public function onLLMError(Exception $error, array $additionalArguments = []);

    /**
     * Run when chain starts running.
     *
     * @param array $serialized
     * @param array $inputs
     * @param array $additionalArguments
     *
     * @return mixed
     */
    abstract public function onChainStart(array $serialized, array $inputs, array $additionalArguments = []);

    /**
     * Run when chain ends running.
     *
     * @param array $outputs
     * @param array $additionalArguments
     *
     * @return mixed
     */
    abstract public function onChainEnd(array $outputs, array $additionalArguments = []);

    /**
     * Run when chain errors.
     *
     * @param Exception $error
     * @param array     $additionalArguments
     *
     * @return mixed
     */
    abstract public function onChainError(Exception $error, array $additionalArguments = []);

    /**
     * Run when tool starts running.
     *
     * @param array  $serialized
     * @param string $inputStr
     * @param array  $additionalArguments
     *
     * @return mixed
     */
    abstract public function onToolStart(array $serialized, string $inputStr, array $additionalArguments = []);

    /**
     * Run when tool ends running.
     *
     * @param string $output
     * @param array  $additionalArguments
     *
     * @return mixed
     */
    abstract public function onToolEnd(string $output, array $additionalArguments = []);

    /**
     * Run when tool errors.
     *
     * @param Exception $error
     * @param array     $additionalArguments
     *
     * @return mixed
     */
    abstract public function onToolError(Exception $error, array $additionalArguments = []);

    /**
     * Run on arbitrary text.
     *
     * @param string $text
     * @param array  $additionalArguments
     *
     * @return mixed
     */
    abstract public function onText(string $text, array $additionalArguments = []);
}
