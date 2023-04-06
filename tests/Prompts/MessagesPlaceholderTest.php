<?php

namespace Kambo\Langchain\Tests\Prompts;

use Kambo\Langchain\Message\HumanMessage;
use Kambo\Langchain\Prompts\MessagesPlaceholder;
use PHPUnit\Framework\TestCase;

class MessagesPlaceholderTest extends TestCase
{
    public function testGetMessages()
    {
        $messages = [
            'foo' => [
                new HumanMessage('foo'),
                new HumanMessage('bar'),
            ],
        ];

        $placeholder = new MessagesPlaceholder('foo');

        $this->assertEquals($messages['foo'], $placeholder->formatMessages($messages));
    }
}
