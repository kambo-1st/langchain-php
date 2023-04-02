<?php

namespace Kambo\Langchain\Tests\Prompts;

use PHPUnit\Framework\TestCase;
use Kambo\Langchain\Prompts\PromptTemplate;
use Kambo\Langchain\Exceptions\InvalidFormat;
use RuntimeException;
use SplFileInfo;

use function json_decode;
use function file_get_contents;
use function is_null;
use function sys_get_temp_dir;
use function rtrim;
use function is_dir;
use function is_writable;
use function strpbrk;
use function sprintf;
use function uniqid;
use function mt_rand;
use function mkdir;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

final class PromptTemplateTest extends TestCase
{
    public function testPromptValid()
    {
        $template = 'This is a {foo} test.';
        $inputVariables = ['foo'];
        $prompt = new PromptTemplate(
            $template,
            $inputVariables,
        );

        $this->assertEquals($template, $prompt->getTemplate());
        $this->assertEquals($inputVariables, $prompt->getInputVariables());
    }

    public function testPromptFromTemplate()
    {
        // Single input variable.
        $template = 'This is a {foo} test.';
        $prompt = PromptTemplate::fromTemplate($template);
        $expectedPrompt = new PromptTemplate(
            $template,
            ['foo'],
        );

        $this->assertEquals($expectedPrompt, $prompt);

        // Multiple input variables.
        $template = 'This {bar} is a {foo} test.';
        $prompt = PromptTemplate::fromTemplate($template);
        $expectedPrompt = new PromptTemplate(
            $template,
            ['bar', 'foo'],
        );

        $this->assertEquals($expectedPrompt, $prompt);

        // Multiple input variables with repeats.
        $template = 'This {bar} is a {foo} test {foo}.';
        $prompt = PromptTemplate::fromTemplate($template);
        $expectedPrompt = new PromptTemplate(
            $template,
            ['bar', 'foo'],
        );

        $this->assertEquals($expectedPrompt, $prompt);
    }

    public function testPromptMissingInputVariables()
    {
        $template = 'This is a {foo} test.';
        $inputVariables = [];

        $this->expectException(InvalidFormat::class);

        new PromptTemplate(
            $template,
            $inputVariables,
        );
    }

    public function testPromptExtraInputVariables()
    {
        $template = 'This is a {foo} test.';
        $inputVariables = ['foo', 'bar'];

        $this->expectException(InvalidFormat::class);

        new PromptTemplate(
            $template,
            $inputVariables,
        );
    }

    public function testPromptWrongInputVariables()
    {
        $template = 'This is a {foo} test.';
        $inputVariables = ['bar'];

        $this->expectException(InvalidFormat::class);

        new PromptTemplate(
            $template,
            $inputVariables,
        );
    }

    public function testPromptFromExamplesValid()
    {
        $template = "Test Prompt:\n\nQuestion: who are you?\nAnswer: foo\n\nQuestion: what are you?\nAnswer: bar\n\nQuestion: {question}\nAnswer:";
        $inputVariables = ['question'];
        $exampleSeparator = "\n\n";
        $prefix = 'Test Prompt:';
        $suffix = "Question: {question}\nAnswer:";
        $examples = [
            "Question: who are you?\nAnswer: foo",
            "Question: what are you?\nAnswer: bar",
        ];

        $promptFrom_examples = PromptTemplate::fromExamples($examples, $suffix, $inputVariables, $exampleSeparator, $prefix);
        $promptFrom_template = new PromptTemplate(
            $template,
            $inputVariables,
        );

        $this->assertEquals($promptFrom_template->getTemplate(), $promptFrom_examples->getTemplate());
        $this->assertEquals($promptFrom_template->getInputVariables(), $promptFrom_examples->getInputVariables());
    }

    public function testPromptFromFile()
    {
        $templateFile = __DIR__ . DIRECTORY_SEPARATOR . 'prompt_file.txt';
        $inputVariables = ['question'];
        $prompt = PromptTemplate::fromFile($templateFile, $inputVariables);

        $this->assertEquals('Question: {question}' . PHP_EOL . 'Answer:' . PHP_EOL, $prompt->getTemplate());
    }

    public function testPromptSave()
    {
        $template = 'This is a {foo} test.';
        $inputVariables = ['foo'];
        $prompt = new PromptTemplate(
            $template,
            $inputVariables,
        );

        $temp = $this->createTempFolder();
        $file = $temp . DIRECTORY_SEPARATOR . 'prompt_file.json';
        $prompt->save($file);

        $expectedArray = [
            'input_variables' => ['foo',],
            'template' => 'This is a {foo} test.',
            'template_format' => 'f-string',
            'validate_template' => true,
        ];

        $this->assertEquals($expectedArray, json_decode(file_get_contents($file), true));
    }

    private function createTempFolder(
        string $dir = null,
        string $prefix = 'tmp_',
        $mode = 0700,
        int $maxAttempts = 10
    ): SplFileInfo {
        /* Use the system temp dir by default. */
        if (is_null($dir)) {
            $dir = sys_get_temp_dir();
        }

        /* Trim trailing slashes from $dir. */
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);

        /* If we don't have permission to create a directory, fail, otherwise we will
         * be stuck in an endless loop.
         */
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new RuntimeException('Target directory is not writable, dir: ' . $dir);
        }

        /* Make sure characters in prefix are safe. */
        if (strpbrk($prefix, '\\/:*?"<>|') !== false) {
            throw new RuntimeException('Character in prefix are not safe, prefix: ' . $prefix);
        }

        /**
         * Attempt to create a random directory until it works. Abort if we reach
         * $maxAttempts. Something screwy could be happening with the filesystem
         * and our loop could otherwise become endless.
         */
        for ($i = 0; $i < $maxAttempts; ++$i) {
            $path = sprintf(
                '%s%s%s%s',
                $dir,
                DIRECTORY_SEPARATOR,
                $prefix,
                uniqid((string)mt_rand(), true)
            );

            if (mkdir($path, $mode, true)) {
                return new SplFileInfo($path);
            }
        }

        throw new RuntimeException('Maximum number of attempts has been reached, prefix: ' . $i);
    }
}
