<?php

namespace Kambo\Langchain\Embeddings;

use OpenAI;
use OpenAI\Client;
use STS\Backoff\Backoff;
use Kambo\Langchain\Exceptions\NotImplemented;
use Kambo\Langchain\Exceptions\MissingAPIKey;

use function getenv;
use function str_replace;
use function count;
use function array_slice;

/**
 * Wrapper around OpenAI embedding models.
 */
class OpenAIEmbeddings implements Embeddings
{
    public Client $client;

    public string $documentModelName = 'text-embedding-ada-002';

    public string $queryModelName = 'text-embedding-ada-002';

    public int $embeddingCtxLength = -1;

    public int $chunkSize = 1000;

    public int $maxRetries = 6;

    private string $openaiApiKey;

    /**
     * OpenAIEmbeddings constructor.
     *
     * Possible config options:
     *   openai_api_key: API key for OpenAI
     *   document_model_name: name of the model used for document embedding
     *   query_model_name: name of the model used for query embedding
     *   embedding_ctx_length: length of the context used for embedding
     *   chunk_size: size of the chunk used for embedding
     *   max_retries: number of retries for embedding
     *
     * @param array   $config
     * @param ?Client $client
     */
    public function __construct(
        array $config = [],
        ?Client $client = null
    ) {
        $token = getenv('OPENAI_API_KEY');
        if (!$token) {
            $token = ($config['openai_api_key'] ?? null);
        }

        $this->openaiApiKey = $token;

        if ($this->openaiApiKey === null) {
            throw new MissingAPIKey('You have to provide an APIKEY.');
        }

        if ($client === null) {
            $client = OpenAI::client($this->openaiApiKey);
        }

        $this->client = $client;

        $this->documentModelName = $config['document_model_name'] ?? $this->documentModelName;
        $this->queryModelName    = $config['query_model_name'] ?? $this->queryModelName;
        $this->embeddingCtxLength = $config['embedding_ctx_length'] ?? $this->embeddingCtxLength;
        $this->chunkSize          = $config['chunk_size'] ?? $this->chunkSize;
        $this->maxRetries         = $config['max_retries'] ?? $this->maxRetries;
    }

    /**
     * Call out to OpenAI's embedding endpoint.
     *
     * @param string $text
     * @param string $engine
     *
     * @return array
     */
    private function embeddingFunc(string $text, string $engine): array
    {
        if ($this->embeddingCtxLength > 0) {
            throw new NotImplemented('tiktoken is not implemented yet.');
        } else {
            $text = str_replace("\n", ' ', $text);
            $response = $this->embedWithRetry(['input' => [$text], 'model' => $engine]);
            return $response['data'][0]['embedding'];
        }
    }

    /**
     * Call out to OpenAI's embedding endpoint for embedding search docs.
     *
     * @param array $texts     The list of texts to embed.
     * @param ?int  $chunkSize The chunk size of embeddings. If None, will use the chunk size specified by the class.
     *
     * @return array List of embeddings, one for each text.
     */
    public function embedDocuments(array $texts, ?int $chunkSize = 0): array
    {
        if ($this->embeddingCtxLength > 0) {
            throw new NotImplemented('tiktoken is not implemented yet.');
        } else {
            $results   = [];
            $chunkSize = $chunkSize ?: $this->chunkSize;

            for ($i = 0; $i < count($texts); $i += $chunkSize) {
                $response = $this->embedWithRetry(
                    [
                        'input' => array_slice($texts, $i, $chunkSize),
                        'model' => $this->documentModelName
                    ]
                );

                foreach ($response['data'] as $data) {
                    $results[] = $data['embedding'];
                }
            }

            return $results;
        }
    }

    /**
     * Call out to OpenAI's embedding endpoint for embedding query text.
     *
     * @param string $text The text to embed.
     *
     * @return array Embeddings for the text.
     */
    public function embedQuery(string $text): array
    {
        $embedding = $this->embeddingFunc($text, $this->queryModelName);
        return $embedding;
    }

    private function embedWithRetry(array $params): array
    {
        $backoff = new Backoff($this->maxRetries, 'exponential', 10000, true);
        $result = $backoff->run(function () use ($params) {
            return $this->client->embeddings()->create($params);
        });

        return $result->toArray();
    }
}
