<?php

namespace Kambo\Langchain\LLMs;

/**
 * Class that contains all relevant information for a single generation.
 */
class Generation
{
    /**
     * @param string $text Output of a single generation.
     * @param array  $generationInfo Raw generation info response from the provider.
     * May include things like reason for finishing (e.g. in OpenAI).
     */
    public function __construct(
        public string $text,
        public array $generationInfo = []
    ) {
    }
}
