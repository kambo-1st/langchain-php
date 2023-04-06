<?php

namespace Kambo\Langchain\Prompts;

use Kambo\Langchain\Message\BaseMessage;

abstract class BaseStringMessagePromptTemplate extends BaseMessagePromptTemplate
{
    protected StringPromptTemplate $prompt;
    protected array $additionalArguments = [];

    /**
     * @param PromptTemplate $prompt
     * @param array          $arguments
     */
    public function __construct(PromptTemplate $prompt, array $arguments = [])
    {
        $this->prompt = $prompt;
        $this->additionalArguments = $arguments;
    }

    /**
     * @param string $template
     * @param array $arguments
     *
     * @return BaseMessagePromptTemplate
     */
    public static function fromTemplate(string $template, array $arguments = []): BaseMessagePromptTemplate
    {
        $prompt = PromptTemplate::fromTemplate($template);
        return new static($prompt, $arguments);
    }

    /**
     * Format arguments into a BaseMessage
     *
     * @param array $arguments
     * @return BaseMessage
     */
    abstract public function format(array $arguments = []): BaseMessage;

    /**
     * Format arguments into a list of messages
     *
     * @param array $arguments
     * @return BaseMessage[]
     */
    public function formatMessages(array $arguments = []): array
    {
        return [$this->format($arguments)];
    }

    /**
     * Get input variables for this prompt template
     *
     * @return array
     */
    public function getInputVariables(): array
    {
        return $this->prompt->getInputVariables();
    }
}
