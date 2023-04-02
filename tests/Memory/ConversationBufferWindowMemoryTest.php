<?php

namespace Kambo\Langchain\Tests\Memory;

use PHPUnit\Framework\TestCase;
use Kambo\Langchain\Memory\ConversationBufferWindowMemory;
use Kambo\Langchain\Message\HumanMessage;
use Kambo\Langchain\Message\AIMessage;

class ConversationBufferWindowMemoryTest extends TestCase
{
    public function testSaveContext(): void
    {
        $memory = new ConversationBufferWindowMemory();
        $inputs = ['input' => 'How are you?'];
        $outputs = ['output' => 'I am fine, thank you.'];

        $memory->saveContext($inputs, $outputs);

        $this->assertCount(2, $memory->getBuffer());
        $this->assertInstanceOf(HumanMessage::class, $memory->getBuffer()[0]);
        $this->assertInstanceOf(AIMessage::class, $memory->getBuffer()[1]);
    }

    public function testClear(): void
    {
        $memory = new ConversationBufferWindowMemory();
        $inputs = ['input' => 'How are you?'];
        $outputs = ['output' => 'I am fine, thank you.'];

        $memory->saveContext($inputs, $outputs);
        $memory->clear();

        $this->assertEmpty($memory->getBuffer());
    }

    public function testLoadMemoryVariables(): void
    {
        $memory = new ConversationBufferWindowMemory();
        $inputs = ['input' => 'How are you?'];
        $outputs = ['output' => 'I am fine, thank you.'];

        for ($i = 0; $i < 6; $i++) {
            $memory->saveContext($inputs, $outputs);
        }

        $loadedMemory = $memory->loadMemoryVariables([]);

        $this->assertArrayHasKey('history', $loadedMemory);
        $this->assertStringContainsString('Human: How are you?', $loadedMemory['history']);
        $this->assertStringContainsString('AI: I am fine, thank you.', $loadedMemory['history']);
    }

    public function testGetBufferString(): void
    {
        $memory = new ConversationBufferWindowMemory();
        $inputs = ['input' => 'How are you?'];
        $outputs = ['output' => 'I am fine, thank you.'];

        $memory->saveContext($inputs, $outputs);

        $bufferString = $memory->getBufferString($memory->getBuffer());
        $this->assertStringContainsString('Human: How are you?', $bufferString);
        $this->assertStringContainsString('AI: I am fine, thank you.', $bufferString);
    }

    public function testGetBufferStringLoadWithDifferentNames(): void
    {
        $inputs = ['input' => 'How are you?'];
        $outputs = ['output' => 'I am fine, thank you.'];

        $customPrefix_memory = new ConversationBufferWindowMemory([
            'human_prefix' => 'User',
            'ai_prefix' => 'Assistant',
        ]);
        $customPrefix_memory->saveContext($inputs, $outputs);
        $bufferString = $customPrefix_memory->loadMemoryVariables();

        $this->assertStringContainsString('User: How are you?', $bufferString['history']);
        $this->assertStringContainsString('Assistant: I am fine, thank you.', $bufferString['history']);
    }
}
