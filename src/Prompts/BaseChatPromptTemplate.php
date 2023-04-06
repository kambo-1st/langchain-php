<?php

namespace Kambo\Langchain\Prompts;

use Kambo\Langchain\Message\BaseMessage;

abstract class BaseChatPromptTemplate extends BasePromptTemplate
{
    /**
     * Format the prompt and return a string
     *
     * @param array $arguments
     * @return string
     */
    public function format(array $arguments = []): string
    {
        return $this->formatPrompt($arguments)->toString();
    }

    /**
     * Format the prompt and return a ChatPromptValue object
     *
     * @param array $arguments
     * @return PromptValue
     */
    public function formatPrompt(array $arguments = []): PromptValue
    {
        $messages = $this->formatMessages($arguments);
        return new ChatPromptValue($messages);
    }

    /**
     * Format arguments into a list of messages
     *
     * @param array $arguments
     * @return BaseMessage[]
     */
    abstract protected function formatMessages(array $arguments = []): array;
}
