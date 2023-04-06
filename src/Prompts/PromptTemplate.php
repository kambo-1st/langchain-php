<?php

namespace Kambo\Langchain\Prompts;

use Kambo\Langchain\Exceptions\InvalidFormat;
use Kambo\Langchain\Exceptions\NotImplemented;
use Kambo\Langchain\Prompts\Formatter\FString;
use SplFileInfo;

use function implode;
use function is_string;
use function file_get_contents;
use function array_merge;
use function array_flip;
use function array_keys;

/**
 * Prompt template class.
 */
class PromptTemplate extends StringPromptTemplate
{
    /**
     * The prompt template.
     */
    public string $template;

    /**
     * The format of the prompt template. Options are: 'f-string'.
     */
    public string $templateFormat = 'f-string';

    /**
     * Whether to try validating the template
     */
    public bool $validateTemplate = true;

    /**
     * @param string $template
     * @param array  $inputVariables
     * @param array  $settings
     */
    public function __construct(
        string $template = '',
        array $inputVariables = [],
        array $settings = []
    ) {
        $this->inputVariables = $inputVariables;
        $this->template = $template;
        $this->templateFormat = $settings['template_format'] ?? $this->templateFormat;
        $this->validateTemplate = $settings['validate_template'] ?? $this->validateTemplate;
        $this->partialVariables = $settings['partial_variables'] ?? [];

        $this->validateVariableNames();
        $this->validateTemplate();
    }

    private function getFormater(): FString
    {
        if ($this->templateFormat === 'f-string') {
            return (new FString());
        }

        throw new NotImplemented(
            'Template formatter: ' . $this->templateFormat . ' is not implemented.'
        );
    }

    /**
     * Return the prompt type key.
     *
     * @return string
     */
    protected function getPromptType(): string
    {
        return 'prompt';
    }

    /**
     * Convert to array
     *
     * @param bool $withType
     *
     * @return array
     */
    public function toArray(bool $withType = true): array
    {
        $data = [
            'input_variables' => $this->inputVariables,
            'template' => $this->template,
            'template_format' => $this->templateFormat,
            'validate_template' => $this->validateTemplate,
        ];

        if ($withType) {
            $data['type'] = $this->getPromptType();
        }

        return $data;
    }

    /**
     * Format the prompt with the inputs.
     *
     * @param array $parameters Any arguments to be passed to the prompt template.
     *
     * @return string A formatted string
     */
    public function format(array $parameters = []): string
    {
        $parameters = $this->mergePartialAndUserVariables($parameters);
        return $this->getFormater()->format($this->template, $parameters);
    }

    /**
     * Take examples in list format with prefix and suffix to create a prompt.
     * Intended be used as a way to dynamically create a prompt from examples.
     *
     * @param array  $examples List of examples to use in the prompt.
     * @param string $suffix String to go after the list of examples. Should generally set up the user's input.
     * @param array  $inputVariables A list of variable names the final prompt template will expect.
     * @param string $exampleSeparator The separator to use in between examples. Defaults to two new line characters.
     * @param string $prefix String that should go before any examples. Generally includes examples.
     *                       Default to an empty string.
     *
     * @return PromptTemplate The final prompt generated.
     */
    public static function fromExamples(
        array $examples,
        string $suffix,
        array $inputVariables,
        string $exampleSeparator = "\n\n",
        string $prefix = ''
    ): PromptTemplate {
        $template = $prefix . $exampleSeparator
            . implode($exampleSeparator, $examples)
            . $exampleSeparator . $suffix;
        return new self($template, $inputVariables);
    }

    /**
     * Load a prompt from a file.
     *
     * @param string|SplFileInfo $templateFile The path to the file containing the prompt template.
     * @param array              $inputVariables A list of variable names the final prompt template will expect.
     *
     * @return PromptTemplate The prompt loaded from the file.
     */
    public static function fromFile(
        string|SplFileInfo $templateFile,
        array $inputVariables
    ): PromptTemplate {
        if (is_string($templateFile)) {
            $templateFile = new SplFileInfo($templateFile);
        }

        $template = file_get_contents((string)$templateFile);
        return new self($template, $inputVariables);
    }

    /**
     * Load a prompt template from a template.
     *
     * @param string $template
     *
     * @return PromptTemplate
     */
    public static function fromTemplate(string $template): PromptTemplate
    {
        $formatter = (new FString());
        $inputVariables = [];
        foreach ($formatter->parse($template) as $v) {
            $inputVariables[] = $v;
        }

        return new self($template, $inputVariables);
    }

    /**
     * @return void
     */
    private function validateTemplate(): void
    {
        if ($this->validateTemplate) {
            $allInputs = array_merge($this->inputVariables, array_keys($this->partialVariables));
            $valid = $this->getFormater()->validate($this->template, array_flip($allInputs));
            if (!$valid) {
                throw new InvalidFormat(
                    'Invalid format - some placeholders are missing'
                );
            }
        }
    }

    /**
     * Template getter.
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * Input variables getter.
     * @return void
     */
    public function getInputVariables(): array
    {
        return $this->inputVariables;
    }
}
