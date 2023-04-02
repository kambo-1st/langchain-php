<?php

namespace Kambo\Langchain\Message;

class AIMessage extends BaseMessage
{
    /**
     * Formats the message as ChatML.
     *
     * @return string
     */
    public function formatChatML(): string
    {
        return "<|im_start|>assistant\n" . $this->content . "\n<|im_end|>";
    }

    /**
     * Returns the type of the message, used for serialization.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'ai';
    }
}
