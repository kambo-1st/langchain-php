<?php

namespace Kambo\Langchain\Memory;

use Kambo\Langchain\Message\HumanMessage;
use Kambo\Langchain\Message\AIMessage;

class ChatMessageHistory
{
    private $messages = [];

    public function addUserMessage($message)
    {
        $this->messages[] = new HumanMessage($message);
    }

    public function addAiMessage($message)
    {
        $this->messages[] = new AIMessage($message);
    }

    public function clear()
    {
        $this->messages = [];
    }

    public function toArray()
    {
        return $this->messages;
    }
}
