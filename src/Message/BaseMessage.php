<?php

namespace Kambo\Langchain\Message;

/**
 * Message object.
 */
abstract class BaseMessage
{
    public function __construct(
        public string $content,
        public array $data = []
    ) {
    }

    /**
     * Formats the message as ChatML.
     *
     * @return string
     */
    abstract public function formatChatML(): string;

    /**
     * Type of the message, used for serialization.
     *
     * @return string
     */
    abstract public function getType(): string;
}
