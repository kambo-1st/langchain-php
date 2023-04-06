<?php

namespace Kambo\Langchain\Prompts;

use Kambo\Langchain\Message\SystemMessage;
use Kambo\Langchain\Message\BaseMessage;

class SystemMessagePromptTemplate extends BaseStringMessagePromptTemplate
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
        return new SystemMessage(
            $text,
            $this->additionalArguments
        );
    }
}
