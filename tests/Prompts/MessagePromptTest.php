<?php

namespace Kambo\Langchain\Tests\Prompts;

use PHPUnit\Framework\TestCase;
use Kambo\Langchain\Prompts\AIMessagePromptTemplate;
use Kambo\Langchain\Prompts\ChatMessagePromptTemplate;
use Kambo\Langchain\Prompts\HumanMessagePromptTemplate;
use Kambo\Langchain\Prompts\SystemMessagePromptTemplate;
use Kambo\Langchain\Prompts\PromptTemplate;
use Kambo\Langchain\Prompts\ChatPromptTemplate;
use Kambo\Langchain\Prompts\ChatPromptValue;
use Kambo\Langchain\Message\HumanMessage;

use function array_merge;
use function array_key_last;

class MessagePromptTest extends TestCase
{
    public function testChatPromptTemplate(): void
    {
        $promptTemplate = new ChatPromptTemplate(
            $this->createMessages(),
            ['foo', 'bar', 'context'],
        );
        $prompt = $promptTemplate->formatPrompt(['foo' => 'foo', 'bar' => 'bar', 'context' => 'context']);

        $this->assertInstanceOf(ChatPromptValue::class, $prompt);

        $messages = $prompt->toMessages();
        $this->assertCount(4, $messages);
        $this->assertEquals("Here's some context: context", $messages[0]->getContent());
        $this->assertEquals("Hello foo, I'm bar. Thanks for the context", $messages[1]->getContent());
        $this->assertEquals("I'm an AI. I'm foo. I'm bar.", $messages[2]->getContent());
        $this->assertEquals("I'm a generic message. I'm foo. I'm bar.", $messages[3]->getContent());

        $expected = "System: Here's some context: context\nHuman: Hello foo, I'm bar. Thanks for the context\nAI: I'm an AI. I'm foo. I'm bar.\ntest: I'm a generic message. I'm foo. I'm bar.";
        $this->assertEquals($expected, (string)$prompt);

        $string = $promptTemplate->format(['foo' => 'foo', 'bar' => 'bar', 'context' => 'context']);
        $this->assertEquals($expected, $string);
    }

    public function testChatPromptTemplateFromMessages(): void
    {
        $chatPromptTemplate = ChatPromptTemplate::fromMessages($this->createMessages());
        $this->assertEqualsCanonicalizing(['context', 'foo', 'bar'], $chatPromptTemplate->getInputVariables());
        $this->assertCount(4, $chatPromptTemplate->getMessages());
    }

    public function testChatPromptTemplateWithMessages(): void
    {
        $messages = array_merge($this->createMessages(), [new HumanMessage('foo')]);
        $chatPromptTemplate = ChatPromptTemplate::fromMessages($messages);
        $this->assertEqualsCanonicalizing(['context', 'foo', 'bar'], $chatPromptTemplate->getInputVariables());
        $this->assertCount(5, $chatPromptTemplate->getMessages());

        $promptValue = $chatPromptTemplate->formatPrompt(['context' => 'see', 'foo' => 'this', 'bar' => 'magic']);
        $promptValueMessages = $promptValue->toMessages();
        $this->assertEquals(new HumanMessage('foo'), $promptValueMessages[array_key_last($promptValueMessages)]);
    }

    protected function createMessages(): array
    {
        $systemMessagePrompt = new SystemMessagePromptTemplate(
            new PromptTemplate(
                "Here's some context: {context}",
                ['context'],
            ),
        );

        $humanMessagePrompt = new HumanMessagePromptTemplate(
            new PromptTemplate(
                "Hello {foo}, I'm {bar}. Thanks for the {context}",
                ['foo', 'bar', 'context'],
            ),
        );

        $aiMessagePrompt = new AIMessagePromptTemplate(
            new PromptTemplate(
                "I'm an AI. I'm {foo}. I'm {bar}.",
                ['foo', 'bar'],
            ),
        );

        $chatMessagePrompt = new ChatMessagePromptTemplate(
            new PromptTemplate(
                "I'm a generic message. I'm {foo}. I'm {bar}.",
                ['foo', 'bar'],
            ),
            'test',
        );

        return [
            $systemMessagePrompt,
            $humanMessagePrompt,
            $aiMessagePrompt,
            $chatMessagePrompt,
        ];
    }
}
