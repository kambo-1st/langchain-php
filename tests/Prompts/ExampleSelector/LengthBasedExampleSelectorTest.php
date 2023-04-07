<?php

namespace Kambo\Langchain\Tests\Prompts\ExampleSelector;

use PHPUnit\Framework\TestCase;
use Kambo\Langchain\Prompts\PromptTemplate;
use Kambo\Langchain\Prompts\ExampleSelector\LengthBasedExampleSelector;

use function array_merge;

class LengthBasedExampleSelectorTest extends TestCase
{
    private const EXAMPLES = [
        ['question' => "Question: who are you?\nAnswer: foo"],
        ['question' => "Question: who are you?\nAnswer: foo"],
    ];

    public function testSelectorValid()
    {
        $selector = $this->selector();
        $short_question = 'Short question?';
        $output = $selector->selectExamples(['question' => $short_question]);
        $this->assertEquals(self::EXAMPLES, $output);
    }

    public function testSelectorAddExample()
    {
        $selector = $this->selector();
        $new_example = ['question' => "Question: what are you?\nAnswer: bar"];
        $selector->addExample($new_example);
        $short_question = 'Short question?';
        $output = $selector->selectExamples(['question' => $short_question]);
        $this->assertEquals(array_merge(self::EXAMPLES, [$new_example]), $output);
    }

    public function testSelectorTrimsOneExample()
    {
        $selector = $this->selector();
        $long_question = 'I am writing a really long question,
        this probably is going to affect the example right?';
        $output = $selector->selectExamples(['question' => $long_question]);
        $this->assertEquals([self::EXAMPLES[0]], $output);
    }

    public function testSelectorTrimsAllExamples()
    {
        $selector = $this->selector();
        $longest_question = 'This question is super super super,
        super super super super super super super super super super super,
        super super super super long, this will affect the example right?';
        $output = $selector->selectExamples(['question' => $longest_question]);
        $this->assertEquals([], $output);
    }

    private function selector(): LengthBasedExampleSelector
    {
        $prompts = new PromptTemplate('{question}', ['question']);
        return new LengthBasedExampleSelector(
            self::EXAMPLES,
            $prompts,
            null,
            30
        );
    }
}
