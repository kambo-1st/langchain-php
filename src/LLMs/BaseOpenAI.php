<?php

namespace Kambo\Langchain\LLMs;

use Kambo\Langchain\Callbacks\CallbackManager;
use STS\Backoff\Backoff;
use OpenAI\Client;
use InvalidArgumentException;
use Exception;
use OpenAI;

use function getenv;
use function array_merge;
use function array_key_exists;
use function array_intersect;
use function array_keys;
use function count;
use function array_chunk;

/**
 * Wrapper around OpenAI large language models.
 *
 * To use, you should have the environment variable ``OPENAI_API_KEY`` set with your API key.
 *
 * Any parameters that are valid to be passed to the openai call can be passed
 * in, even if not explicitly saved on this class.
 */
class BaseOpenAI extends BaseLLM
{
    protected $client = null;
    protected string $modelName = 'text-davinci-003';
    protected float $temperature = 0.7;
    protected int $maxTokens = 256;
    protected int $topP = 1;
    protected int $frequencyPenalty = 0;
    protected int $presencePenalty = 0;
    protected int $n = 1;
    protected int $bestOf = 1;
    protected array $modelKwargs = [];
    protected ?string $openaiApiKey = null;
    protected int $batchSize = 20;
    protected ?int $requestTimeout = null;
    protected array $logitBias = [];
    protected int $maxRetries = 6;
    protected bool $streaming = false;

    /**
     * Possible configuration options:
     *
     * model_name (string): The name of the model to use for generating text, e.g., 'text-davinci-003'.
     * temperature (float): The sampling temperature to use for controlling randomness during text generation.
     * A higher value (e.g., 1.0) results in more random output, while a lower value (e.g., 0.1) produces
     * more focused and deterministic output. Default is 0.7.
     * max_tokens (int): The maximum number of tokens to generate in a completion. A value of -1 returns as many tokens
     * as possible, given the prompt and the model's maximal context size. Default is 256.
     * top_p (float): The total probability mass of tokens to consider at each step during generation. Default is 1.
     * frequency_penalty (float): Penalizes repeated tokens according to their frequency. A higher value results in
     * less repetition of common tokens. Default is 0.
     * presence_penalty (float): Penalizes repeated tokens, regardless of their frequency. A higher value results
     * in less repetition. Default is 0.
     * n (int): The number of completions to generate for each prompt. Default is 1.
     * best_of (int): Generates 'best_of' completions server-side and returns the "best" completion. Default is 1.
     * model_kwargs (array): An array of additional model parameters that are valid for the 'create' call but not
     * explicitly specified. Default is an empty array.
     * openai_api_key (string|null): The API key for accessing the OpenAI API. Set to null if not using the
     * OpenAI API directly. Default is null.
     * batch_size (int): The batch size to use when passing multiple documents for text generation. Default is 20.
     * request_timeout (float|tuple|null): The timeout for requests to the OpenAI completion API. Default is null,
     * which corresponds to a 600-second timeout. Can be a float (e.g., 10.0) or a tuple of floats (e.g., (5.0, 30.0)).
     * logit_bias (array): An array of token biases to adjust the probability of specific tokens being generated.
     * Default is an empty array.
     * max_retries (int): The maximum number of retries to make when generating text. Default is 6.
     * streaming (bool): Whether to stream the results or not. If true, results will be streamed as they are generated.
     * Default is false.
     *
     * @param array            $config
     * @param ?Client          $client
     * @param ?CallbackManager $callbackManager
     */
    public function __construct(
        array $config = [],
        ?Client $client = null,
        ?CallbackManager $callbackManager = null
    ) {
        parent::__construct($callbackManager);
        $this->client = $client;
        $this->modelName = $config['model_name'] ?? $this->modelName;
        $this->temperature = $config['temperature'] ?? $this->temperature;
        $this->maxTokens = $config['max_tokens'] ?? $this->maxTokens;
        $this->topP = $config['top_p'] ?? $this->topP;
        $this->frequencyPenalty = $config['frequency_penalty'] ?? $this->frequencyPenalty;
        $this->presencePenalty = $config['presence_penalty'] ?? $this->presencePenalty;
        $this->n = $config['n'] ?? $this->n;
        $this->bestOf = $config['best_of'] ?? $this->bestOf;
        $this->modelKwargs = $config['model_kwargs'] ?? $this->modelKwargs;
        $this->batchSize = $config['batch_size'] ?? $this->batchSize;
        $this->requestTimeout = $config['request_timeout'] ?? $this->requestTimeout;
        $this->logitBias = $config['logit_bias'] ?? $this->logitBias;
        $this->maxRetries = $config['max_retries'] ?? $this->maxRetries;
        $this->streaming = $config['streaming'] ?? $this->streaming;

        $token = getenv('OPENAI_API_KEY');
        if (!$token) {
            $token = ($config['openai_api_key'] ?? null);
        }

        $this->openaiApiKey = $token;

        if ($this->openaiApiKey === null) {
            throw new Exception('You have to provide an APIKEY.');
        }

        if ($client === null) {
            $client = OpenAI::client($this->openaiApiKey);
        }

        $this->client = $client;
    }

    /**
     * Get the default parameters for this LLM.
     *
     * @return array
     */
    public function defaultParams(): array
    {
        return [
            'model' => $this->modelName,
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
            'top_p' => $this->topP,
            'frequency_penalty' => $this->frequencyPenalty,
            'presence_penalty' => $this->presencePenalty,
            'n' => $this->n,
            'best_of' => $this->bestOf,
            'logit_bias' => $this->logitBias,
        ];
    }

    /**
     * Call out to OpenAI's endpoint with k unique prompts.
     *
     * @param array  $prompts The parameters to pass into the model.
     * @param ?array $stop The stop words to use when generating.
     *
     * @return LLMResult The full LLM output.
     */
    public function generateResult(array $prompts, ?array $stop = null): LLMResult
    {
        // Call out to OpenAI's endpoint with k unique prompts.
        $params = $this->getInvocationParams();
        $subPrompts = $this->getSubPrompts($params, $prompts, $stop);
        $choices = [];
        $tokenUsage = [
            'completion_tokens' => 0,
            'prompt_tokens' => 0,
            'total_tokens' => 0,
        ];

        // Get the token usage from the response.
        // Includes prompt, completion, and total tokens used.
        $keys = ['completion_tokens', 'prompt_tokens', 'total_tokens'];
        foreach ($subPrompts as $_prompts) {
            $response = $this->completionWithRetry($this->client, ['prompt' => $_prompts], ...$params);
            $choices = array_merge($choices, $response['choices']);
            $this->updateTokenUsage($keys, $response, $tokenUsage);
        }

        return $this->createLlmResult($choices, $prompts, $tokenUsage);
    }

    private function completionWithRetry($client, $params, ...$additionalParams)
    {
        $params = array_merge($additionalParams, $params);
        $params['logit_bias'] = array_key_exists('logit_bias', $params)
            ? (object) $params['logit_bias']
            : (object)[];

        $backoff = new Backoff($this->maxRetries, 'exponential', 10000, true);
        $result = $backoff->run(function () use ($client, $params) {
            return $client->completions()->create($params);
        });

        return $result->toArray();
    }

    private function updateTokenUsage($keys, $response, &$tokenUsage)
    {
        // Update token usage.
        $keysTo_use = array_intersect($keys, array_keys($response['usage']));
        foreach ($keysTo_use as $_key) {
            if (! array_key_exists($_key, $tokenUsage)) {
                $tokenUsage[$_key] = $response['usage'][$_key];
            } else {
                $tokenUsage[$_key] += $response['usage'][$_key];
            }
        }
    }

    /**
     * Get the sub prompts for llm call.
     *
     * @param array  $params
     * @param array  $prompts
     * @param ?array $stop
     *
     * @return array
     */
    private function getSubPrompts(array &$params, array $prompts, ?array $stop = null)
    {
        /** Get the sub prompts for llm call. */
        if ($stop !== null) {
            if (array_key_exists('stop', $params)) {
                throw new InvalidArgumentException('`stop` found in both the input and default params.');
            }
            $params['stop'] = $stop;
        }

        if ($params['max_tokens'] == -1) {
            if (count($prompts) !== 1) {
                throw new InvalidArgumentException('max_tokens set to -1 not supported for multiple inputs.');
            }
            $params['max_tokens'] = $this->max_tokens_for_prompt($prompts[0]);
        }

        $subPrompts = array_chunk($prompts, $this->batchSize);

        return $subPrompts;
    }

    private function createLlmResult($choices, $prompts, $tokenUsage)
    {
        $generations = [];
        foreach ($choices as $choice) {
            $generations[] = [
                new Generation(
                    $choice['text'],
                    [
                        'finish_reason' => $choice['finish_reason'] ?? null,
                        'logprobs' => $choice['logprobs'] ?? null,
                    ],
                )
            ];
        }

        return new LLMResult($generations, ['token_usage' => $tokenUsage, 'model_name' => $this->modelName]);
    }

    /** Get the parameters used to invoke the model. */
    public function getInvocationParams()
    {
        return $this->defaultParams();
    }

    public function identifyingParams(): array
    {
        /** @var array<string, mixed> $defaultParams */
        $defaultParams = $this->defaultParams();

        return array_merge(['model_name' => $this->modelName], $defaultParams);
    }

    public function llmType(): string
    {
        return 'openai';
    }
}
