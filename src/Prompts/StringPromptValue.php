<?php

namespace Kambo\Langchain\Prompts;

use Kambo\Langchain\Message\HumanMessage;

class StringPromptValue implements PromptValue
{
    public function __construct(public string $text)
    {
    }

    /**
     * Return prompt as messages.
     *
     * @return HumanMessage[]
     */
    public function toMessages(): array
    {
        return [new HumanMessage($this->text)];
    }

    /**
     * Return prompt as string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->text;
    }
}
