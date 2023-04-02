<?php

namespace Kambo\Langchain\Message;

class ChatMessage extends BaseMessage
{
    public function __construct(
        public string $content,
        public string $role,
        public array $data = []
    ) {
    }

    /**
     * Formats the message as ChatML.
     *
     * @return string
     */
    public function formatChatML(): string
    {
        return '<|im_start|>' . $this->role . "\n" . $this->content . "\n<|im_end|>";
    }

    /**
     * Returns the type of the message, used for serialization.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'chat';
    }

    public function getRole(): string
    {
        return $this->role;
    }
}
