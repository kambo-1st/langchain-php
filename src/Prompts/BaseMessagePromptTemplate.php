<?php

namespace Kambo\Langchain\Prompts;

use Kambo\Langchain\Message\BaseMessage;

abstract class BaseMessagePromptTemplate
{
    /**
     * Format arguments into a list of messages
     *
     * @param array $arguments
     * @return BaseMessage[]
     */
    abstract public function formatMessages(array $arguments = []): array;

    /**
     * Get input variables for this prompt template
     *
     * @return array
     */
    abstract public function getInputVariables(): array;
}
