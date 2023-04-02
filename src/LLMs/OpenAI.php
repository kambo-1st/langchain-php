<?php

namespace Kambo\Langchain\LLMs;

use function array_merge;

/**
 * Generic OpenAI class that uses model name.
 */
final class OpenAI extends BaseOpenAI
{
    /** Generic OpenAI class that uses model name. */
    public function getInvocationParams()
    {
        return array_merge(
            ['model' => $this->modelName],
            parent::getInvocationParams()
        );
    }
}
