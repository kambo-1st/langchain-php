<?php

namespace Kambo\Langchain\Prompts;

use Kambo\Langchain\Exceptions\InvalidArgumentException;
use Kambo\Langchain\Message\BaseMessage;

use function is_array;
use function json_encode;

/**
 * Prompt template that assumes variable is already list of messages.
 */
class MessagesPlaceholder extends BaseMessagePromptTemplate
{
    protected string $variableName;

    /**
     * @param string $variableName
     */
    public function __construct(string $variableName)
    {
        $this->variableName = $variableName;
    }

    /**
     * Format arguments into a list of messages
     *
     * @param array $arguments
     * @return BaseMessage[]
     */
    public function formatMessages(array $arguments = []): array
    {
        $value = $arguments[$this->variableName];

        if (!is_array($value)) {
            throw new InvalidArgumentException(
                'Variable '
                . $this->variableName
                . ' should be a list of base messages, got '
                . json_encode($value)
            );
        }

        foreach ($value as $v) {
            if (!($v instanceof BaseMessage)) {
                throw new InvalidArgumentException(
                    'Variable '
                    . $this->variableName
                    . ' should be a list of base messages, got '
                    . json_encode($value)
                );
            }
        }

        return $value;
    }

    /**
     * Get input variables for this prompt template
     *
     * @return array
     */
    public function getInputVariables(): array
    {
        return [$this->variableName];
    }
}
