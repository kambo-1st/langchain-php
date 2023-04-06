<?php

namespace Kambo\Langchain\Prompts;

use Kambo\Langchain\Message\BaseMessage;
use Kambo\Langchain\Message\AIMessage;

final class AIMessagePromptTemplate extends BaseStringMessagePromptTemplate
{
    /**
     * Format the prompt and return a BaseMessage
     *
     * @param array $arguments
     * @return BaseMessage
     */
    public function format(array $arguments = []): BaseMessage
    {
        $text = $this->prompt->format($arguments);
        return new AIMessage($text, $arguments);
    }
}
