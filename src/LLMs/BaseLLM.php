<?php

namespace Kambo\Langchain\LLMs;

use Kambo\Langchain\Callbacks\BaseCallbackManager;
use Kambo\Langchain\Callbacks\CallbackManager;
use Kambo\Langchain\Callbacks\StdOutCallbackHandler;
use Exception;
use Kambo\Langchain\Exceptions\ValueError;
use Stringable;
use SplFileInfo;
use Psr\SimpleCache\CacheInterface;

use function get_class;
use function print_r;
use function is_string;
use function file_exists;
use function mkdir;
use function file_put_contents;
use function json_encode;
use function md5;
use function implode;

use const JSON_PRETTY_PRINT;

/**
 * Base class for all language models.
 */
abstract class BaseLLM extends BaseLanguageModel implements Stringable
{
    public bool $verbose = false;
    public bool $useCache = false;
    public ?BaseCallbackManager $callbackManager = null;
    private ?CacheInterface $cache;

    public function __construct(
        array $options = [],
        ?CallbackManager $callbackManager = null,
        ?CacheInterface $cache = null
    ) {
        $this->callbackManager = $callbackManager ?? new CallbackManager(
            [new StdOutCallbackHandler() ]
        );
        $this->cache = $cache;
        $this->verbose = $options['verbose'] ?? $this->verbose;
        $this->useCache = $options['use_cache'] ?? $this->useCache;
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

        if ($this->shouldUseCache()) {
            $newResults = $this->getResultsWithCache($prompts, $stop);
        } else {
            try {
                $newResults = $this->generateResult($prompts, $stop);
            } catch (Exception $e) {
                $this->callbackManager->onLLMError($e, ['verbose' => $this->verbose]);
                throw $e;
            }
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
     * Convert the LLM to an array.
     *
     * @return array
     */
    abstract public function toArray(): array;

    abstract public function getIdentifyingParams(): array;

    /**
     * Get a string representation of the object for printing.
     *
     * @return string
     */
    public function __toString(): string
    {
        $clsName = "\033[1m" . get_class($this) . "\033[0m";
        return $clsName . "\nParams: " . print_r($this->getIdentifyingParams(), true);
    }

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

    /**
     * Save the LLM.
     *
     * @param string|SplFileInfo $filePath
     *
     * @return void
     */
    public function save(string|SplFileInfo $filePath): void
    {
        if (is_string($filePath)) {
            $filePath = new SplFileInfo($filePath);
        }

        // create directory if not exists
        if (!file_exists($filePath->getPath())) {
            mkdir($filePath->getPath(), 0644, true);
        }

        $data = $this->toArray();
        if ($filePath->getExtension() === 'json') {
            file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
        } else {
            throw new ValueError($filePath . ' must be json or yaml');
        }
    }

    private function shouldUseCache(): bool
    {
        return $this->cache !== null;
    }

    /**
     * @param mixed      $prompt
     * @param array|null $stop
     *
     * @return string
     */
    private function getCacheKey(mixed $prompt, ?array $stop): string
    {
        return $this->llmType() . md5($prompt) . ($stop !== null ? md5(implode('', $stop)) : '');
    }

    /**
     * @param array  $prompts
     * @param ?array $stop
     *
     * @return LLMResult
     */
    public function getResultsWithCache(array $prompts, ?array $stop): LLMResult
    {
        $shouldBeLoaded = [];
        $alreadyResolved = [];
        foreach ($prompts as $prompt) {
            $key = $this->getCacheKey($prompt, $stop);
            $value = $this->cache->get($key, false);

            if ($value === false) {
                $shouldBeLoaded[] = $prompt;
            } else {
                $alreadyResolved[] = $value;
            }
        }

        if (empty($shouldBeLoaded)) {
            $newResults = LLMResult::createFromCachedValues($alreadyResolved);
        } else {
            try {
                $newResults = $this->generateResult($shouldBeLoaded, $stop);
            } catch (Exception $e) {
                $this->callbackManager->onLLMError($e, ['verbose' => $this->verbose]);
                throw $e;
            }

            $llmOutput = $newResults->getLLMOutput();
            foreach ($newResults->getGenerations() as $index => $generation) {
                $prompt = $shouldBeLoaded[$index];

                $key = $this->getCacheKey($prompt, $stop);
                $this->cache->set($key, [$generation, $llmOutput]);
            }

            $newResults->merge($alreadyResolved);
        }
        return $newResults;
    }
}
