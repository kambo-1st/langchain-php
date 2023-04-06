<?php

namespace Kambo\Langchain\Prompts;

use Kambo\Langchain\Message\BaseMessage;
use Kambo\Langchain\Message\Utils;

class ChatPromptValue implements PromptValue
{
    public function __construct(protected array $messages)
    {
    }

    /**
     * Return prompt as a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return Utils::getBufferString($this->messages);
    }

    /**
     * Return prompt as messages
     *
     * @return BaseMessage[]
     */
    public function toMessages(): array
    {
        return $this->messages;
    }
}
