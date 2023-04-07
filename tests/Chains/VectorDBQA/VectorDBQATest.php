<?php

namespace Kambo\Langchain\Tests\Chains\VectorDBQA;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Kambo\Langchain\Chains\VectorDbQa\VectorDBQA;
use Kambo\Langchain\Embeddings\OpenAIEmbeddings;
use Kambo\Langchain\LLMs\OpenAI;
use Kambo\Langchain\VectorStores\SimpleStupidVectorStore;
use OpenAI\Client;
use OpenAI\Transporters\HttpTransporter;
use OpenAI\ValueObjects\ApiKey;
use OpenAI\ValueObjects\Transporter\BaseUri;
use OpenAI\ValueObjects\Transporter\Headers;
use PHPUnit\Framework\TestCase;

use function json_encode;
use function array_merge;

class VectorDBQATest extends TestCase
{
    public function testRun(): void
    {
        $openAI = $this->mockOpenAIWithResponses(
            [
                self::prepareResponse(
                    [
                        'id' => 'cmpl-6yE7cLrSIWqxXyAqwrI1HhOc5M3eu',
                        'object' => 'text_completion',
                        'created' => 1679801984,
                        'model' => 'text-davinci-003',
                        'choices' =>
                            [
                                [
                                    'text' => 'Kaleidosocks',
                                    'index' => 0,
                                    'logprobs' => null,
                                    'finish_reason' => 'stop',
                                ],
                            ],
                        'usage' => [
                            'prompt_tokens' => 15,
                            'completion_tokens' => 7,
                            'total_tokens' => 22,
                        ],
                    ]
                )
            ]
        );
        $embeddings = $this->mockOpenAIEmbeddingsWithResponses(
            [
                $this->prepareResponse(
                    [
                        'object' => 'list',
                        'data' => [
                            [
                                'object' => 'embedding',
                                'index' => 0,
                                'embedding' =>
                                    [
                                        -0.015587599,
                                        -0.03145355,
                                        -0.010950541,
                                        -0.014322372,
                                        -0.0121335285,
                                        -0.0009655265,
                                        -0.025747374,
                                         0.0009908311,
                                        -0.017751137,
                                        -0.010210384,
                                         0.0010643724,
                                    ],
                            ],
                        ],
                        'usage' => [
                            'prompt_tokens' => 1468,
                            'total_tokens' => 1468,
                        ],
                    ]
                )
            ]
        );

        $chain = VectorDBQA::fromChainType(
            $openAI,
            'stuff',
            null,
            [
                'vectorstore' => new SimpleStupidVectorStore($embeddings)
            ]
        );

        $this->assertEquals('Kaleidosocks', $chain->run('stuff'));
    }

    public function testToArray(): void
    {
        $openAI = $this->mockOpenAIWithResponses();
        $embeddings = $this->mockOpenAIEmbeddingsWithResponses();

        $chain = VectorDBQA::fromChainType(
            $openAI,
            'stuff',
            null,
            [
                'vectorstore' => new SimpleStupidVectorStore($embeddings)
            ]
        );

        $this->assertEquals(
            [
            'memory' => null,
            'verbose' => false,
            'k' => 4,
            'combine_documents_chain' =>
                [
                    'memory' => null,
                    'verbose' => false,
                    'input_key' => 'input_documents',
                    'output_key' => 'output_text',
                    'document_variable_name' => 'context',
                    'llm_chain' =>
                        [
                            'memory' => null,
                            'verbose' => false,
                            'llm' =>
                                [
                                    'model_name' => 'text-davinci-003',
                                    'model' => 'text-davinci-003',
                                    'temperature' => 0.7,
                                    'max_tokens' => 256,
                                    'top_p' => 1,
                                    'frequency_penalty' => 0,
                                    'presence_penalty' => 0,
                                    'n' => 1,
                                    'best_of' => 1,
                                    'logit_bias' =>
                                        [
                                        ],
                                ],
                            'prompt' =>
                                [
                                    'input_variables' =>
                                        [
                                            0 => 'context',
                                            1 => 'question',
                                        ],
                                    'template' => 'Use the following pieces of context to answer the question at the end.
            If you don\'t know the answer, just say that you don\'t know, don\'t try to make up an answer
            .

{context}

Question: {question}
Helpful Answer:',
                                    'template_format' => 'f-string',
                                    'validate_template' => true,
                                    'type' => 'prompt',
                                ],
                            'output_key' => 'text',
                            '_type' => 'llm_chain',
                        ],
                    'document_prompt' =>
                        [
                            'input_variables' =>
                                [
                                    0 => 'page_content',
                                ],
                            'template' => '{page_content}',
                            'template_format' => 'f-string',
                            'validate_template' => true,
                            'type' => 'prompt',
                        ],
                    '_type' => 'stuff_documents_chain',
                ],
            'search_type' => 'similarity',
            'search_kwargs' =>
                [
                ],
            'return_source_documents' => false,
            'input_key' => 'query',
            'output_key' => 'result',
            '_type' => 'vector_db_qa',
            ],
            $chain->toArray()
        );
    }

    private static function prepareResponse(array $response): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($response));
    }

    private static function mockOpenAIEmbeddingsWithResponses(
        array $responses = [],
        array $options = []
    ): OpenAIEmbeddings {
        $mock = new MockHandler($responses);

        $client = self::client($mock);
        return new OpenAIEmbeddings(array_merge(['openai_api_key' => 'test'], $options), $client);
    }

    private static function client(MockHandler $mockHandler): Client
    {
        $apiKey = ApiKey::from('test');
        $baseUri = BaseUri::from('api.openai.com/v1');
        $headers = Headers::withAuthorization($apiKey);

        $handlerStack = HandlerStack::create($mockHandler);
        $client = new GuzzleClient(['handler' => $handlerStack]);

        $transporter = new HttpTransporter($client, $baseUri, $headers);

        return new Client($transporter);
    }

    private static function mockOpenAIWithResponses(array $responses = [], array $options = []): OpenAI
    {
        $mock = new MockHandler($responses);

        $client = self::client($mock);
        return new OpenAI(array_merge(['openai_api_key' => 'test'], $options), $client);
    }
}
