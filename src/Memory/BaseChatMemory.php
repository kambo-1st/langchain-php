<?php

namespace Kambo\Langchain\Memory;

use Exception;

use function is_null;
use function count;
use function sprintf;
use function array_keys;
use function array_values;
use function array_diff;
use function array_merge;

abstract class BaseChatMemory implements BaseMemory
{
    protected $chatMemory;
    protected $outputKey = null;
    protected $inputKey = null;
    protected $returnMessages = false;

    public function __construct()
    {
        $this->chatMemory = new ChatMessageHistory();
    }

    /**
     * Save context from this conversation to buffer.
     *
     * @param array $inputs
     * @param array $outputs
     *
     * @return void
     */
    public function saveContext(array $inputs, array $outputs): void
    {
        if (is_null($this->inputKey)) {
            $promptInputKey = $this->getPromptInputKey($inputs, $this->getMemoryVariables());
        } else {
            $promptInputKey = $this->inputKey;
        }

        if (is_null($this->outputKey)) {
            if (count($outputs) != 1) {
                throw new Exception(sprintf('One output key expected, got %d', count($outputs)));
            }
            $outputKey = array_keys($outputs)[0];
        } else {
            $outputKey = $this->outputKey;
        }

        $this->chatMemory->addUserMessage($inputs[$promptInputKey]);
        $this->chatMemory->addAiMessage($outputs[$outputKey]);
    }

    private function getPromptInputKey($inputs, $memoryVariables)
    {
        // "stop" is a special key that can be passed as input but is not used to
        // format the prompt.
        $promptInputKeys = array_values(array_diff(array_keys($inputs), array_merge($memoryVariables, ['stop'])));
        if (count($promptInputKeys) != 1) {
            throw new Exception(sprintf('One input key expected, got %d', count($promptInputKeys)));
        }

        return $promptInputKeys[0];
    }

    /**
     * Clear memory contents.
     * @return void
     */
    public function clear(): void
    {
        $this->chatMemory->clear();
    }

    // implement toArray
    public function toArray(): array
    {
        return [
            'outputKey' => $this->outputKey,
            'inputKey' => $this->inputKey,
            'returnMessages' => $this->returnMessages,
        ];
    }
}
