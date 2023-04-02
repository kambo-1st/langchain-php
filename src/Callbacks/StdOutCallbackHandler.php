<?php

namespace Kambo\Langchain\Callbacks;

use Kambo\Langchain\LLMs\LLMResult;

class StdOutCallbackHandler extends BaseCallbackHandler
{
    private ?string $color;

    public function __construct(?string $color = null)
    {
        $this->color = $color;
    }

    public function onLLMStart(
        array $serialized,
        array $prompts,
        array $additionalArguments = []
    ): void {
        // Print out the prompts
    }

    public function onLLMEnd(LLMResult $response, array $additionalArguments = []): void
    {
        // Do nothing
    }

    public function onLLMNewToken(string $token, array $additionalArguments = []): void
    {
        // Do nothing
    }

    public function onLLMError($error, array $additionalArguments = []): void
    {
        // Do nothing
    }

    public function onChainStart(
        array $serialized,
        array $inputs,
        array $additionalArguments = []
    ): void {
        // Print out that we are entering a chain
        $className = $serialized['name'];
        echo "\n\n\033[1m> Entering new " . $className . " chain...\033[0m\n";
    }

    public function onChainEnd(array $outputs, array $additionalArguments = []): void
    {
        // Print out that we finished a chain
        echo "\n\033[1m> Finished chain.\033[0m\n";
    }

    public function onChainError($error, array $additionalArguments = []): void
    {
        // Do nothing
    }

    public function onToolStart(
        array $serialized,
        string $inputStr,
        array $additionalArguments = []
    ): void {
        // Do nothing
    }

    public function onToolEnd(
        string $output,
        array $additionalArguments = []
    ): void {
        // If not the final action, print out observation
        $color = $additionalArguments['color'] ?? $this->color;
        $observationPrefix = $additionalArguments['observation_prefix'] ?? '';
        $llmPrefix = $additionalArguments['llm_prefix'] ?? '';
        $this->printText("\n" . $observationPrefix, $color);
        $this->printText($output, $color);
        $this->printText("\n" . $llmPrefix);
    }

    public function onToolError($error, array $additionalArguments = []): void
    {
        // Do nothing
    }

    public function onText(
        string $text,
        array $additionalArguments = []
    ): void {
        // Run when agent ends
        $color = $additionalArguments['color'] ?? $this->color;
        $end = $additionalArguments['end'] ?? '';
        $this->printText($text, $color, $end);
    }

    private function printText(string $text, ?string $color = null, string $end = ''): void
    {
        // Helper function to print text with optional color and end
        if ($color) {
            echo "\033[" . $color . 'm';
        }

        echo $text;
        if ($color) {
            echo "\033[0m";
        }

        echo $end;
    }
}
