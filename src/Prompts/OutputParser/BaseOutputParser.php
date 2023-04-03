<?php

namespace Kambo\Langchain\Prompts\OutputParser;

use Kambo\Langchain\Exceptions\NotImplemented;
use Kambo\Langchain\Prompts\PromptValue;

/**
 * Class to parse the output of an LLM call.
 *
 * Output parsers help structure language model responses.
 * They are used in conjunction with PromptTemplates to
 * help structure the output of a language model call.
 */
abstract class BaseOutputParser
{
    /**
     * Parse the output of an LLM call.
     *
     * A method which takes in a string (assumed output of language model)
     * and parses it into some structure.
     *
     * @param string $text Output of language model
     * @return mixed Structured output
     */
    abstract public function parse(string $text);

    /**
     * Optional method to parse the output of an LLM call with a prompt.
     *
     * The prompt is largely provided in the event the OutputParser wants
     * to retry or fix the output in some way, and needs information from
     * the prompt to do so.
     *
     * @param string $completion Output of language model
     * @param PromptValue $prompt Prompt value
     *
     * @return mixed Structured output
     */
    public function parseWithPrompt(string $completion, PromptValue $prompt)
    {
        return $this->parse($completion);
    }

    /**
     * Instructions on how the LLM output should be formatted.
     *
     * @return string
     */
    public function getFormatInstructions()
    {
        throw new NotImplemented();
    }

    /**
     * Return the type key.
     *
     * @return string
     */
    public function getType()
    {
        throw new NotImplemented();
    }

    /**
     * Return dictionary representation of output parser.
     *
     * @param array $additionalParameters Additional parameters to include in the output
     * @return array
     */
    abstract public function toArray(array $additionalParameters = []): array;
}
