<?php

namespace Kambo\Langchain\Prompts;

use SplFileInfo;
use ValueError;

use function array_intersect;
use function implode;
use function array_diff;
use function array_keys;
use function array_merge;
use function array_map;
use function in_array;
use function is_string;
use function file_exists;
use function mkdir;
use function file_put_contents;
use function json_encode;

use const JSON_PRETTY_PRINT;

/**
 * Base class for all prompt templates.
 */
abstract class BasePromptTemplate
{
    /**
     * A list of the names of the variables the prompt template expects.
     */
    public array $inputVariables;
    public ?BaseOutputParser $outputParser = null;
    public array $partialVariables = [];

    /**
     * Validate variable names do not include restricted names.
     */
    protected function validateVariableNames(): void
    {
        if (in_array('stop', $this->inputVariables)) {
            throw new ValueError(
                "Cannot have an input variable named 'stop', as it is used internally, please rename."
            );
        }

        if (in_array('stop', $this->partialVariables)) {
            throw new ValueError(
                "Cannot have an partial variable named 'stop', as it is used internally, please rename."
            );
        }

        $overall = array_intersect($this->inputVariables, $this->partialVariables);
        if (!empty($overall)) {
            throw new ValueError(
                'Found overlapping input and partial variables: ' . implode(', ', $overall)
            );
        }
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
        $promptDict['input_variables'] = array_diff($this->inputVariables, array_keys($arguments));
        $promptDict['partial_variables'] = array_merge($this->partialVariables, $arguments);
        return new static($promptDict);
    }

    protected function mergePartialAndUserVariables(array $kwargs): array
    {
        // Get partial params:
        $partialKwargs = array_map(function ($v) {
            return is_string($v) ? $v : $v();
        }, $this->partialVariables);
        return array_merge($partialKwargs, $kwargs);
    }

    abstract public function toArray();

    abstract public function format(array $parameters): string;

    /**
     * Save the prompt.
     *
     * @param string|SplFileInfo $filePath
     *
     * @return void
     */
    public function save(string|SplFileInfo $filePath): void
    {
        if (!empty($this->partialVariables)) {
            throw new ValueError('Cannot save prompt with partial variables.');
        }

        if (is_string($filePath)) {
            $filePath = new SplFileInfo($filePath);
        }

        // create directory if not exists
        if (!file_exists($filePath->getPath())) {
            mkdir($filePath->getPath(), 0644, true);
        }

        // Fetch dictionary to save
        $data = $this->toArray(false);
        if ($filePath->getExtension() === 'json') {
            file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
        } else {
            throw new ValueError($filePath . ' must be json or yaml');
        }
    }
}
