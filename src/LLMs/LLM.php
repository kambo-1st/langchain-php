<?php

namespace Kambo\Langchain\LLMs;

/**
 * LLM class that expect subclasses to implement a simpler call method.
 * The purpose of this class is to expose a simpler interface for working
 * with LLMs, rather than expect the user to implement the full generateResult method.
 */
abstract class LLM extends BaseLLM
{
    /**
     * @param string $prompt
     * @param array  $parameters
     *
     * @return string
     */
    abstract public function call(string $prompt, array $parameters = []): string;

    /**
     * Run the LLM on the given prompts.
     * This is the method that should be implemented by subclasses ;-)
     *
     * @param array      $prompts
     * @param array|null $stop
     *
     * @return LLMResult
     */
    public function generateResult(array $prompts, array $stop = null): LLMResult
    {
        $generations = [];
        foreach ($prompts as $prompt) {
            $result = $this->call($prompt, ['stop' => $stop]);
            $generations[] = new Generation(
                $result,
            );
        }

        return new LLMResult([$generations], []);
    }
}
