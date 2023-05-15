<?php

namespace Kambo\Langchain\LLMs;

use Kambo\Langchain\Callbacks\CallbackManager;
use Kambo\Langchain\Exceptions\InvalidArgumentException;
use Kambo\Langchain\Exceptions\LogicException;
use Kambo\Langchain\Exceptions\MissingAPIKey;
use Psr\SimpleCache\CacheInterface;
use Kambo\HuggingfaceLangchainAdapter\HuggingfaceLangchainAdapter;

final class HuggingFaceHub extends LLM
{
    private const DEFAULT_REPO_ID = "gpt2";
    private const VALID_TASKS = ["text2text-generation", "text-generation"];

    private string $repoId;
    private string $task = "text-generation";

    private HuggingfaceLangchainAdapter $adapter;

    public function __construct(
        array $options = [],
        ?CallbackManager $callbackManager = null,
        ?CacheInterface $cache = null,
        ?HuggingfaceLangchainAdapter $adapter = null,
    ) {
        if (!class_exists(HuggingfaceLangchainAdapter::class)) {
            throw new InvalidArgumentException(
                'Could not found HuggingfaceLangchainAdapter.
                Please install the HuggingfaceLangchainAdapter library to use this model.'
            );
        }

        $token = $config['huggingfacehub_api_token'] ?? null;
        if (!$token) {
            $token = getenv('HUGGINGFACE_API_KEY');
        }

        if ($token === null) {
            throw new MissingAPIKey('You have to provide an APIKEY.');
        }

        $this->repoId = $options['repo_id'] ?? self::DEFAULT_REPO_ID;
        $this->task = $options['task'] ?? $this->task;

        if (!in_array($this->task, self::VALID_TASKS)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Got invalid task %s, currently only %s are supported",
                    $this->task,
                    implode(', ', self::VALID_TASKS)
                )
            );
        }

        parent::__construct($options, $callbackManager, $cache);

        if ($adapter === null) {
            $this->adapter = HuggingfaceLangchainAdapter::create([
                'token' => $token
            ]);
        }
    }

    public function call(string $prompt, array $parameters = []): string {
        $response = $this->adapter->predict(
            $prompt,
            array_merge(['model' => $this->repoId, 'task'=>$this->task], $parameters)
        );
        if (array_key_exists("error", $response)) {
            throw new LogicException(sprintf("Error raised by inference API: %s", $response['error']));
        }
var_export($response);die;
        if ($this->task == "text-generation") {
            $text = substr($response[0]["generated_text"], strlen($prompt));
        } else if ($this->task == "text2text-generation") {
            $text = $response[0]["generated_text"];
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    "Got invalid task %s, currently only %s are supported",
                    $this->task,
                    implode(', ', self::VALID_TASKS)
                )
            );
        }

        $stop = $parameters['stop'];
        if ($stop !== null) {
            $text = $this->enforceStopTokens($text, $stop);
        }

        return $text;
    }

    public function llmType(): string
    {
        return "huggingface_hub";
    }

    public function toArray(): array
    {
        return $this->getIdentifyingParams();
    }

    public function getIdentifyingParams(): array
    {
        return ["repo_id" => $this->repoId, "task" => $this->task];
    }

    private function enforceStopTokens(string $text, array $stop) {
        // Prepare the pattern by escaping special characters and joining with '|'
        $pattern = array_map('preg_quote', $stop);
        $pattern = implode('|', $pattern);

        // Perform the split and return the first part
        $parts = preg_split("/$pattern/", $text);
        return $parts[0];
    }
}
