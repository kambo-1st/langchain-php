<?php

namespace Kambo\Langchain\LLMs;

use Kambo\Langchain\Callbacks\CallbackManager;
use Psr\SimpleCache\CacheInterface;
use Kambo\Langchain\Exceptions\InvalidArgumentException;
use Kambo\LLamaCPPLangchainAdapter\LLamaCPPLangchainAdapter;

use function class_exists;
use function array_merge;

/**
 * Wrapper around the llama.cpp model.
 */
final class LLaMACpp extends LLM
{
    private $modelPath;
    private $nCtx = 1024;
    private $nParts = -1;
    private $seed = 0;
    private $f16Kv = false;
    private $logitsAll = false;
    private $vocabOnly = false;
    private $useMlock = false;
    private $nThreads = null;
    private $nBatch = 8;
    private $suffix = null;
    private $maxTokens = 256;
    private $temperature = 0.8;
    private $topP = 0.95;
    private $logprobs = null;
    private $echo = false;
    private $stop = [];
    private $repeatPenalty = 1.1;
    private $topK = 40;
    private $lastNTokensSize = 64;
    private LLamaCPPLangchainAdapter $adapter;

   /**
    * Possible options:
    *
    * model_path - The path to the Llama model file.
    * n_ctx - Token context window.
    * n_parts - Number of parts to split the model into. If -1, the number of parts is automatically determined.
    * seed - Seed. If -1, a random seed is used.
    * f16_kv - Use half-precision for key/value cache.
    * logits_all - Return logits for all tokens, not just the last token.
    * vocab_only - Only load the vocabulary, no weights.
    * use_mlock - Force system to keep model in RAM.
    * n_threads - Number of threads to use. If None, the number of threads is automatically determined.
    * n_batch - Number of tokens to process in parallel. Should be a number between 1 and n_ctx.
    * suffix - A suffix to append to the generated text. If None, no suffix is appended.
    * max_tokens - The maximum number of tokens to generate.
    * temperature - The temperature to use for sampling.
    * top_p - The top-p value to use for sampling.
    * logprobs - The number of logprobs to return. If None, no logprobs are returned.
    * echo - Whether to echo the prompt.
    * stop - A list of strings to stop generation when encountered.
    * repeat_penalty - The penalty to apply to repeated tokens.
    * top_k - The top-k value to use for sampling.
    * last_n_tokens_size - The number of tokens to look back when applying the repeat_penalty.
    *
    * @param array                         $options
    * @param CallbackManager|null          $callbackManager
    * @param CacheInterface|null           $cache
    * @param LLamaCPPLangchainAdapter|null $adapter
    *
    * @throws InvalidArgumentException
    */
    public function __construct(
        array $options = [],
        ?CallbackManager $callbackManager = null,
        ?CacheInterface $cache = null,
        ?LLamaCPPLangchainAdapter $adapter = null,
    ) {
        if (!class_exists(LLamaCPPLangchainAdapter::class)) {
            throw new InvalidArgumentException(
                'Could not found LLamaCPPLangchainAdapter.
                Please install the LLamaCPPLangchainAdapter library to use this model.'
            );
        }

        $this->modelPath = $options['model_path'] ?? $this->modelPath;
        $this->nCtx = $options['n_ctx'] ?? $this->nCtx;
        $this->nParts = $options['n_parts'] ?? $this->nParts;
        $this->seed = $options['seed'] ?? $this->seed;
        $this->f16Kv = $options['f16_kv'] ?? $this->f16Kv;
        $this->logitsAll = $options['logits_all'] ?? $this->logitsAll;
        $this->vocabOnly = $options['vocab_only'] ?? $this->vocabOnly;
        $this->useMlock = $options['use_mlock'] ?? $this->useMlock;
        $this->nThreads = $options['n_threads'] ?? $this->nThreads;
        $this->nBatch = $options['n_batch'] ?? $this->nBatch;
        $this->suffix = $options['suffix'] ?? $this->suffix;
        $this->maxTokens = $options['max_tokens'] ?? $this->maxTokens;
        $this->temperature = $options['temperature'] ?? $this->temperature;
        $this->topP = $options['top_p'] ?? $this->topP;
        $this->logprobs = $options['logprobs'] ?? $this->logprobs;
        $this->echo = $options['echo'] ?? $this->echo;
        $this->stop = $options['stop'] ?? $this->stop;
        $this->repeatPenalty = $options['repeat_penalty'] ?? $this->repeatPenalty;
        $this->topK = $options['top_k'] ?? $this->topK;
        $this->lastNTokensSize = $options['last_n_tokens_size'] ?? $this->lastNTokensSize;

        parent::__construct($options, $callbackManager, $cache);

        if ($adapter === null) {
            $adapter = LLamaCPPLangchainAdapter::create(
                [
                    'model_path' => $this->modelPath,
                    'n_ctx' => $this->nCtx,
                    'n_parts' => $this->nParts,
                    'seed' => $this->seed,
                    'f16_kv' => $this->f16Kv,
                    'logits_all' => $this->logitsAll,
                    'vocab_only' => $this->vocabOnly,
                    'use_mlock' => $this->useMlock,
                    'n_threads' => $this->nThreads,
                ]
            );
        }

        $this->adapter = $adapter;
    }

    /**
     * Returns the default parameters for the model.
     * @return array
     */
    private function defaultParams()
    {
        return [
            'suffix' => $this->suffix,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'top_p' => $this->topP,
            'logprobs' => $this->logprobs,
            'echo' => $this->echo,
            'stop_sequences' => $this->stop,
            'repeat_penalty' => $this->repeatPenalty,
            'top_k' => $this->topK,
        ];
    }

    /**
     * Call the Llama model and return the output.
     *
     * @param string $prompt The prompt to use for generation.
     * @param mixed  $stop   A list of strings to stop generation when encountered.
     *
     * @return string The generated text.
     */
    public function call(string $prompt, $stop = null): string
    {
        $params = $this->defaultParams();

        if ($this->stop && $stop !== null) {
            throw new InvalidArgumentException('`stop` found in both the input and default params.');
        } elseif ($this->stop) {
            $params['stop_sequences'] = $this->stop;
        } elseif ($stop !== null) {
            $params['stop_sequences'] = $stop;
        } else {
            $params['stop_sequences'] = [];
        }

        $text = $this->adapter->predict($prompt, $params);

        return $text;
    }

    public function llmType(): string
    {
        return 'llama.cpp';
    }

    public function toArray(): array
    {
        return $this->getIdentifyingParams();
    }

    public function getIdentifyingParams(): array
    {
        return array_merge(['model_path' => $this->modelPath], $this->defaultParams());
    }
}
