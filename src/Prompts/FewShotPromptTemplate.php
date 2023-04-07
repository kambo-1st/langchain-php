<?php

namespace Kambo\Langchain\Prompts;

use Kambo\Langchain\Exceptions\InvalidArgumentException;
use Kambo\Langchain\Exceptions\InvalidFormat;
use Kambo\Langchain\Exceptions\NotImplemented;
use Kambo\Langchain\Exceptions\ValueError;
use Kambo\Langchain\Prompts\Formatter\FString;
use Kambo\Langchain\Prompts\ExampleSelector\BaseExampleSelector;

use function array_merge;
use function array_keys;
use function array_flip;
use function array_map;
use function implode;
use function array_filter;
use function array_diff;

class FewShotPromptTemplate extends StringPromptTemplate
{
    /**
     * Examples to format into the prompt.
     *  Either this or example_selector should be provided.
     */
    public ?array $examples = null;

    /**
     * Example selector to choose the examples to format into the prompt.
     *  Either this or examples should be provided.
     */
    public ?BaseExampleSelector $exampleSelector = null;

    /** PromptTemplate used to format an individual example. */
    public PromptTemplate $examplePrompt;

    /** A prompt template string to put after the examples. */
    public string $suffix;

    /** A list of the names of the variables the prompt template expects. */
    public array $inputVariables;

    /** String separator used to join the prefix, the examples, and suffix. */
    public string $exampleSeparator = "\n\n";

    /** A prompt template string to put before the examples. */
    public string $prefix = '';

    /** The format of the prompt template. Options are: 'f-string', 'jinja2'. */
    public string $templateFormat = 'f-string';

    /** Whether or not to try validating the template. */
    public bool $validateTemplate = true;

    /**
     * @param string              $prefix
     * @param string              $suffix
     * @param PromptTemplate|null $examplePrompt
     * @param array               $inputVariables
     * @param array               $settings
     * @param array               $examples
     *
     * @throws InvalidFormat
     * @throws ValueError
     */
    public function __construct(
        string $prefix = '',
        string $suffix = '',
        PromptTemplate $examplePrompt = null,
        array $inputVariables = [],
        array $settings = [],
        array $examples = [],
    ) {
        $this->prefix = $prefix;
        $this->suffix = $suffix;
        $this->examplePrompt = $examplePrompt;
        $this->inputVariables = $inputVariables;
        $this->templateFormat = $settings['template_format'] ?? $this->templateFormat;
        $this->validateTemplate = $settings['validate_template'] ?? $this->validateTemplate;
        $this->partialVariables = $settings['partial_variables'] ?? [];
        $this->exampleSeparator = $settings['example_separator'] ?? $this->exampleSeparator;
        $this->examples = $examples;

        $this->validateVariableNames();
        $this->validateTemplate();
    }

    /**
     * Check that one and only one of examples/example_selector are provided.
     *
     * @param array $values
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public static function checkExamplesAndSelector(array $values): array
    {
        $examples = $values['examples'] ?? null;
        $example_selector = $values['example_selector'] ?? null;

        if ($examples && $example_selector) {
            throw new InvalidArgumentException("Only one of 'examples' and 'example_selector' should be provided");
        }

        if ($examples === null && $example_selector === null) {
            throw new InvalidArgumentException("One of 'examples' and 'example_selector' should be provided");
        }

        return $values;
    }


    /**
     * @return void
     */
    private function validateTemplate(): void
    {
        if ($this->validateTemplate) {
            $allInputs = array_merge($this->inputVariables, array_keys($this->partialVariables));
            $valid = $this->getFormatter()->validate($this->prefix . $this->suffix, array_flip($allInputs));
            if (!$valid) {
                throw new InvalidFormat(
                    'Invalid format - some placeholders are missing'
                );
            }
        }
    }

    /**
     * Get the examples to use.
     *
     * @param array $kwargs
     *
     * @return array
     * @throws InvalidArgumentException
     */
    private function getExamples(array $kwargs): array
    {
        if ($this->examples !== null) {
            return $this->examples;
        } elseif ($this->exampleSelector !== null) {
            return $this->exampleSelector->selectExamples($kwargs);
        } else {
            throw new InvalidArgumentException();
        }
    }

    /**
     * Format the prompt with the inputs.
     *
     * @param array $parameters
     *
     * @return string
     */
    public function format(array $parameters): string
    {
        $parameters = $this->mergePartialAndUserVariables($parameters);
        $examples = $this->getExamples($parameters);

        $example_strings = array_map(
            function ($example) {
                return $this->examplePrompt->format($example);
            },
            $examples
        );

        $pieces = array_merge([$this->prefix
        ], $example_strings, [$this->suffix]);
        $template = implode($this->exampleSeparator, array_filter($pieces));

        return $this->getFormatter()->format($template, $parameters);
    }

    /**
     * Return the prompt type key.
     *
     * @return string
     */
    protected function getPromptType(): string
    {
        return 'few_shot';
    }

    /**
     * Convert the class object into an associative array.
     *
     * @return array
     */
    public function toArray(): array
    {
        if ($this->exampleSelector) {
            throw new InvalidArgumentException('Saving an example selector is not currently supported');
        }

        return [
            'examples' => $this->examples,
            'exampleSelector' => $this->exampleSelector,
            'example_prompt' => $this->examplePrompt,
            'suffix' => $this->suffix,
            'input_variables' => $this->inputVariables,
            'example_separator' => $this->exampleSeparator,
            'prefix' => $this->prefix,
            'templateFormat' => $this->templateFormat,
            'validate_template' => $this->validateTemplate,
            'prompt_type' => $this->getPromptType(),
            'partial_variables' => $this->partialVariables,
        ];
    }

    /**
     * Return a partial of the prompt template.
     *
     * @param array $arguments
     *
     * @return $this
     */
    public function partial(array $arguments): BasePromptTemplate
    {
        $promptDict = $this->toArray();
        $inputVariables = array_diff($this->inputVariables, array_keys($arguments));
        $promptDict['partial_variables'] = array_merge($this->partialVariables, $arguments);

        return new self(
            $promptDict['prefix'],
            $promptDict['suffix'],
            $promptDict['example_prompt'],
            $inputVariables,
            $promptDict,
            $promptDict['examples']
        );
    }

    private function getFormatter(): FString
    {
        if ($this->templateFormat === 'f-string') {
            return (new FString());
        }

        throw new NotImplemented(
            'Template formatter: ' . $this->templateFormat . ' is not implemented.'
        );
    }
}
