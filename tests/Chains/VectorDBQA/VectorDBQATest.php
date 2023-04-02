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

    private static function prepareResponse(array $response): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($response));
    }

    private static function mockOpenAIEmbeddingsWithResponses(array $responses, array $options = []): OpenAIEmbeddings
    {
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

    private static function mockOpenAIWithResponses(array $responses, array $options = []): OpenAI
    {
        $mock = new MockHandler($responses);

        $client = self::client($mock);
        return new OpenAI(array_merge(['openai_api_key' => 'test'], $options), $client);
    }
}
