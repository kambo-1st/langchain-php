<?php

namespace Kambo\Langchain\LLMs;

use Kambo\Langchain\Callbacks\BaseCallbackManager;
use Kambo\Langchain\Callbacks\CallbackManager;
use Kambo\Langchain\Callbacks\StdOutCallbackHandler;
use Exception;

use function get_class;

/**
 * Base class for all language models.
 * TODO [SIMEK, i] implement toArray, __toString, cache and saving to JSON
 */
abstract class BaseLLM extends BaseLanguageModel
{
    public bool $verbose = false;
    public ?BaseCallbackManager $callbackManager = null;

    public function __construct(?CallbackManager $callbackManager = null)
    {
        $this->callbackManager = $callbackManager ?? new CallbackManager(
            [new StdOutCallbackHandler() ]
        );
    }

    /**
     * Run the LLM on the given prompt and input.
     *
     * @param array $prompts The prompts to pass into the model.
     * @param array|null $stop Optional list of stop words to use when generating.
     *
     * @return LLMResult The LLM result.
     */
    public function generate(array $prompts, ?array $stop = null): LLMResult
    {
        $this->callbackManager->onLLMStart(
            ['name' => get_class($this)],
            $prompts,
            ['verbose' => $this->verbose]
        );

        try {
            $newResults = $this->generateResult($prompts, $stop);
        } catch (Exception $e) {
            $this->callbackManager->onLLMError($e, ['verbose' => $this->verbose]);
            throw $e;
        }

        $this->callbackManager->onLLMEnd($newResults, ['verbose' => $this->verbose]);

        return $newResults;
    }

    /**
     * Take in a list of prompt values and return an LLMResult.
     *
     * @param array  $prompts
     * @param ?array $stop
     *
     * @return LLMResult
     */
    public function generatePrompt(array $prompts, ?array $stop = null): LLMResult
    {
        return $this->generate($prompts, $stop);
    }

    /**
     * Return type of llm.
     * This is the method that should be implemented by subclasses ;-)
     *
     * @return string
     */
    abstract public function llmType(): string;

    /**
     * Run the LLM on the given prompts.
     * This is the method that should be implemented by subclasses ;-)
     *
     * @param array      $prompts
     * @param array|null $stop
     *
     * @return LLMResult
     */
    abstract public function generateResult(array $prompts, array $stop = null): LLMResult;

    /**
     * @param string     $prompt
     * @param array|null $stop
     *
     * @return string
     */
    public function __invoke(string $prompt, ?array $stop = null): string
    {
        $result = $this->generate([$prompt], $stop);

        return $result->getFirstGenerationText();
    }
}
