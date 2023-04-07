<?php

namespace Kambo\Langchain\Prompts\ExampleSelector;

abstract class BaseExampleSelector
{
    /**
     * Add new example to store for a key.
     *
     * @param array $example
     * @return mixed
     */
    abstract public function addExample(array $example);

    /**
     * Select which examples to use based on the inputs.
     *
     * @param array $input_variables
     * @return array
     */
    abstract public function selectExamples(array $input_variables): array;
}
