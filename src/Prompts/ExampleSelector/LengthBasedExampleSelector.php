<?php

namespace Kambo\Langchain\Prompts\ExampleSelector;

use Kambo\Langchain\Prompts\PromptTemplate;

use function call_user_func;
use function array_map;
use function implode;
use function array_values;
use function count;
use function preg_split;

class LengthBasedExampleSelector extends BaseExampleSelector
{
    /** @var array A list of the examples that the prompt template expects. */
    public array $examples;

    /** @var PromptTemplate Prompt template used to format the examples. */
    public PromptTemplate $examplePrompt;

    /** @var callable Function to measure prompt length. Defaults to word count. */
    public $getTextLength;

    /** @var int Max length for the prompt, beyond which examples are cut. */
    public int $maxLength = 2048;

    /** @var array */
    private array $exampleTextLengths = [];

    public function __construct(
        array $examples,
        PromptTemplate $example_prompt,
        callable $getTextLength = null,
        int $max_length = 2048
    ) {
        $this->examples = $examples;
        $this->examplePrompt = $example_prompt;
        $this->getTextLength = $getTextLength ?? [$this, 'getLengthBased'];
        $this->maxLength = $max_length;
        $this->exampleTextLengths = $this->calculateExampleTextLengths();
    }

    public function addExample(array $example): void
    {
        $this->examples[] = $example;
        $string_example = $this->examplePrompt->format($example);
        $this->exampleTextLengths[] = call_user_func($this->getTextLength, $string_example);
    }

    private function calculateExampleTextLengths(): array
    {
        $string_examples = array_map(function ($eg) {
            return $this->examplePrompt->format($eg);
        }, $this->examples);

        return array_map($this->getTextLength, $string_examples);
    }

    public function selectExamples(array $input_variables): array
    {
        $inputs = implode(' ', array_values($input_variables));
        $remaining_length = $this->maxLength - call_user_func($this->getTextLength, $inputs);
        $i = 0;
        $examples = [];

        while ($remaining_length > 0 && $i < count($this->examples)) {
            $new_length = $remaining_length - $this->exampleTextLengths[$i];
            if ($new_length < 0) {
                break;
            } else {
                $examples[] = $this->examples[$i];
                $remaining_length = $new_length;
            }
            $i++;
        }

        return $examples;
    }

    private function getLengthBased(string $text): int
    {
        return count(preg_split('/\n|\s/', $text));
    }
}
