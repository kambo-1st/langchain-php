<?php

namespace Kambo\Langchain\LLMs;

use Kambo\Langchain\Exceptions\IllegalState;

use function array_key_exists;
use function array_merge;

/**
 * Class that contains all relevant information for an LLM Result.
 */
final class LLMResult
{
    /**
     * @param array $generations List of the things generated.
     * @param array $llmOutput For arbitrary LLM provider specific output.
     */
    public function __construct(public array $generations, public array $llmOutput)
    {
    }

    public function getGenerations(): array
    {
        return $this->generations;
    }

    public function getLLMOutput(): array
    {
        return $this->llmOutput;
    }

    public function getGeneration(int $index): array
    {
        // throw error if index is out of range
        if ($index < 0 || !array_key_exists($index, $this->generations)) {
            throw new IllegalState('Index out of range');
        }

        return $this->generations[$index];
    }

    public function getFirstGenerationText(): string
    {
        return $this->getGeneration(0)[0]->text;
    }

    public static function createFromCachedValues(array $cachedValues): self
    {
        $self = new self([$cachedValues[0][0]], $cachedValues[0][1]);

        foreach ($cachedValues as $index => $cachedValue) {
            if ($index === 0) {
                continue;
            }

            $self->generations[] = [$cachedValue[0]];
        }

        return $self;
    }

    public function merge(array $alreadyResolved)
    {
        foreach ($alreadyResolved as [$existingGenerations]) {
            $this->generations = array_merge([$existingGenerations], $this->generations);
        }
    }
}
