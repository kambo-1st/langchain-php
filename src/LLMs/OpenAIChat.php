<?php

namespace Kambo\Langchain\LLMs;

use Kambo\Langchain\Callbacks\CallbackManager;
use OpenAI\Client;
use OpenAI\OpenAI;
use Kambo\Langchain\Exceptions\IllegalState;
use STS\Backoff\Backoff;

use function getenv;
use function array_merge;
use function count;
use function sprintf;
use function var_export;

final class OpenAIChat extends BaseLLM
{
    private Client $client;
    private string $modelName = 'gpt-3.5-turbo';
    private array $modelAdditionalParams = [];
    private int $maxRetries = 6;
    private array $prefixMessages = [];

    private ?string $openaiApiKey;

    public function __construct(
        array $config = [],
        ?Client $client = null,
        ?CallbackManager $callbackManager = null
    ) {
        parent::__construct($config, $callbackManager);

        $token = getenv('OPENAI_API_KEY');
        if (!$token) {
            $token = ($config['openai_api_key'] ?? null);
        }

        $this->openaiApiKey = $token;

        if ($client === null) {
            $client = \OpenAI::client($this->openaiApiKey);
        }

        $this->client = $client;
    }

    public function generateResult(array $prompts, array $stop = null): LLMResult
    {
        [$messages, $params] = $this->getChatParams($prompts, $stop);

        $fullResponse = $this->completionWithRetry($this->client, $messages, ...$params);

        $generations = [
            [
                new Generation(
                    $fullResponse['choices'][0]['message']['content'],
                )
            ]
        ];

        return new LLMResult(
            $generations,
            ['token_usage' => $fullResponse['usage']],
        );
    }

    private function completionWithRetry($client, $params, ...$kwargs)
    {
        $params = array_merge($kwargs, ['messages' => [$params]]);
        $backoff = new Backoff($this->maxRetries, 'exponential', 10000, true);
        $result = $backoff->run(function () use ($client, $params) {
            return $client->chat()->create($params);
        });

        return $result->toArray();
    }

    public function getIdentifyingParams(): array
    {
        return [
            'model_name' => $this->modelName,
            'model_kwargs' => $this->defaultParams(),
        ];
    }

    private function defaultParams(): array
    {
        return $this->modelAdditionalParams;
    }

    private function getChatParams(array $prompts, ?array $stop = null): array
    {
        if (count($prompts) > 1) {
            throw new IllegalState(
                sprintf(
                    'OpenAIChat currently only supports single prompt, got %s',
                    var_export($prompts, true)
                )
            );
        }

        $messages = array_merge(
            $this->prefixMessages,
            ['role' => 'user', 'content' => $prompts[0]]
        );

        $params = array_merge(['model' => $this->modelName], $this->defaultParams());

        if ($stop !== null) {
            if (isset($params['stop'])) {
                throw new IllegalState('`stop` found in both the input and default params.');
            }

            $params['stop'] = $stop;
        }

        if (isset($params['max_tokens']) && $params['max_tokens'] === -1) {
            unset($params['max_tokens']);
        }

        return [$messages, $params];
    }

    public function llmType(): string
    {
        return 'openai-chat';
    }

    public function toArray(): array
    {
        return $this->getIdentifyingParams();
    }
}
