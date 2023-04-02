<?php

namespace Kambo\Langchain\LLMs;

use Kambo\Langchain\Exceptions\NotImplemented;

/**
 * Base LLM wrapper should take in a prompt and return a string.
 */
abstract class BaseLanguageModel
{
    /**
     * Take in a list of prompt values and return an LLMResult.
     *
     * @param array  $prompts
     * @param ?array $stop
     *
     * @return LLMResult
     */
    abstract public function generatePrompt(array $prompts, ?array $stop = null): LLMResult;

    /**
     * Get the number of tokens present in the text.
     *
     * @param string $text
     *
     * @return int
     */
    public function getNumTokens(string $text): int
    {
        throw new NotImplemented('Not implemented');
    }
}
