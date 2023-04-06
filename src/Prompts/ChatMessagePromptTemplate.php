<?php

namespace Kambo\Langchain\Prompts;

use Kambo\Langchain\Message\BaseMessage;
use Kambo\Langchain\Message\ChatMessage;

class ChatMessagePromptTemplate extends BaseStringMessagePromptTemplate
{
    protected string $role;

    public function __construct(PromptTemplate $prompt, string $role = '', array $arguments = [])
    {
        $this->role = $role;
        parent::__construct($prompt, $arguments);
    }

    /**
     * Format the prompt and return a BaseMessage
     *
     * @param array $arguments
     * @return BaseMessage
     */
    public function format(array $arguments = []): BaseMessage
    {
        $text = $this->prompt->format($arguments);
        return new ChatMessage(
            $text,
            $this->role,
            $arguments
        );
    }
}
