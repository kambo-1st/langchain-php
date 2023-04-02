<?php

namespace Kambo\Langchain\Message;

class HumanMessage extends BaseMessage
{
    /**
     * Formats the message as ChatML.
     *
     * @return string
     */
    public function formatChatML(): string
    {
        return "<|im_start|>user\n" . $this->content . "\n<|im_end|>";
    }

    /**
     * Returns the type of the message, used for serialization.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'human';
    }
}
