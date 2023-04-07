<?php

namespace Kambo\Langchain\Tests\Prompts;

use Kambo\Langchain\Prompts\FewShotPromptTemplate;
use PHPUnit\Framework\TestCase;
use Kambo\Langchain\Prompts\PromptTemplate;
use Kambo\Langchain\Exceptions\InvalidFormat;

class FewShotPromptTemplateTest extends TestCase
{
    private function getExamplePrompt(): PromptTemplate
    {
        return new PromptTemplate(
            '{question}: {answer}',
            ['question', 'answer'],
        );
    }

    public function testSuffixOnly(): void
    {
        $suffix = 'This is a {foo} test.';
        $inputVariables = ['foo'];
        $prompt = new FewShotPromptTemplate(
            suffix: $suffix,
            examplePrompt: $this->getExamplePrompt(),
            inputVariables: $inputVariables,
        );

        $output = $prompt->format(['foo' => 'bar']);
        $expectedOutput = 'This is a bar test.';
        $this->assertEquals($expectedOutput, $output);
    }

    public function testPromptMissingInputVariables(): void
    {
        $suffix = 'This is a {foo} test.';

        $this->expectException(InvalidFormat::class);
        new FewShotPromptTemplate(
            suffix: $suffix,
            examplePrompt: $this->getExamplePrompt(),
        );
    }

    public function testPromptExtraInputVariables(): void
    {
        $suffix = 'This is a {foo} test.';
        $inputVariables = ['foo', 'bar'];

        $this->expectException(InvalidFormat::class);
        new FewShotPromptTemplate(
            suffix: $suffix,
            examplePrompt: $this->getExamplePrompt(),
            inputVariables: $inputVariables,
        );
    }

    public function testFewShotFunctionality(): void
    {
        $prefix = 'This is a test about {content}.';
        $suffix = 'Now you try to talk about {new_content}.';
        $examples = [
            ['question' => 'foo', 'answer' => 'bar'],
            ['question' => 'baz', 'answer' => 'foo'],
        ];
        $inputVariable = ['content', 'new_content'];

        $prompt = new FewShotPromptTemplate(
            prefix: $prefix,
            suffix: $suffix,
            examplePrompt: $this->getExamplePrompt(),
            inputVariables: $inputVariable,
            settings: ['example_separator' => "\n"],
            examples: $examples,
        );

        $output = $prompt->format(['content' => 'animals', 'new_content' => 'party']);
        $expectedOutput = "This is a test about animals.\nfoo: bar\nbaz: foo\nNow you try to talk about party.";
        $this->assertEquals($expectedOutput, $output);
    }

    public function testPartialInitString(): void
    {
        $prefix = 'This is a test about {content}.';
        $suffix = 'Now you try to talk about {new_content}.';
        $examples = [
            ['question' => 'foo', 'answer' => 'bar'],
            ['question' => 'baz', 'answer' => 'foo'],
        ];
        $inputVariable = ['new_content'];
        $partialVariables = ['content' => 'animals'];

        $prompt = new FewShotPromptTemplate(
            prefix: $prefix,
            suffix: $suffix,
            examplePrompt: $this->getExamplePrompt(),
            inputVariables: $inputVariable,
            settings: ['example_separator' => "\n", 'partial_variables' => $partialVariables],
            examples: $examples,
        );

        $output = $prompt->format(['new_content' => 'party']);
        $expectedOutput = "This is a test about animals.\nfoo: bar\nbaz: foo\nNow you try to talk about party.";
        $this->assertEquals($expectedOutput, $output);
    }

    public function testPartialInitFunc(): void
    {
        $prefix = 'This is a test about {content}.';
        $suffix = 'Now you try to talk about {new_content}.';
        $examples = [
            ['question' => 'foo', 'answer' => 'bar'],
            ['question' => 'baz', 'answer' => 'foo'],
        ];
        $inputVariable = ['new_content'];
        $partialVariables = ['content' => function () {
            return 'animals';
        }];

        $prompt = new FewShotPromptTemplate(
            prefix: $prefix,
            suffix: $suffix,
            examplePrompt: $this->getExamplePrompt(),
            inputVariables: $inputVariable,
            settings: ['example_separator' => "\n", 'partial_variables' => $partialVariables],
            examples: $examples,
        );

        $output = $prompt->format(['new_content' => 'party']);
        $expectedOutput = "This is a test about animals.\nfoo: bar\nbaz: foo\nNow you try to talk about party.";
        $this->assertEquals($expectedOutput, $output);
    }

    public function testPartial(): void
    {
        $prefix = 'This is a test about {content}.';
        $suffix = 'Now you try to talk about {new_content}.';
        $examples = [
            ['question' => 'foo', 'answer' => 'bar'],
            ['question' => 'baz', 'answer' => 'foo'],
        ];
        $inputVariable = ['content', 'new_content'];

        $prompt = new FewShotPromptTemplate(
            prefix: $prefix,
            suffix: $suffix,
            examplePrompt: $this->getExamplePrompt(),
            inputVariables: $inputVariable,
            settings: ['example_separator' => "\n"],
            examples: $examples,
        );

        $newPrompt = $prompt->partial(['content' => 'foo']);
        $newOutput = $newPrompt->format(['new_content' => 'party']);
        $expectedOutput = "This is a test about foo.\nfoo: bar\nbaz: foo\nNow you try to talk about party.";
        $this->assertEquals($expectedOutput, $newOutput);

        $output = $prompt->format(['content' => 'bar', 'new_content' => 'party']);
        $expectedOutput = "This is a test about bar.\nfoo: bar\nbaz: foo\nNow you try to talk about party.";
        $this->assertEquals($expectedOutput, $output);
    }
}
