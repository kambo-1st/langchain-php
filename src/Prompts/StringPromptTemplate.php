<?php

namespace Kambo\Langchain\Prompts;

/**
 * String prompt should expose the format method, returning a prompt.
 */
abstract class StringPromptTemplate extends BasePromptTemplate
{
    /**
     * Create Chat Messages.
     *
     * @param $options
     *
     * @return PromptValue
     */
    public function formatPrompt($options): PromptValue
    {
        return new StringPromptValue($this->format($options));
    }
}
