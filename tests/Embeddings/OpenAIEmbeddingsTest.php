<?php

namespace Kambo\Langchain\Tests\Embeddings;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use OpenAI\Client;
use OpenAI\Transporters\HttpTransporter;
use OpenAI\ValueObjects\ApiKey;
use OpenAI\ValueObjects\Transporter\BaseUri;
use OpenAI\ValueObjects\Transporter\Headers;
use PHPUnit\Framework\TestCase;
use Kambo\Langchain\Embeddings\OpenAIEmbeddings;

use function json_encode;
use function array_merge;

class OpenAIEmbeddingsTest extends TestCase
{
    public function testEmbedDocuments(): void
    {
        $openAI = $this->mockOpenAIWithResponses(
            [
                self::prepareResponse(
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

        $result = $openAI->embedDocuments(['foo bar baz']);

        $this->assertEquals(
            [
                [
                    0 => -0.015587599,
                    1 => -0.03145355,
                    2 => -0.010950541,
                    3 => -0.014322372,
                    4 => -0.0121335285,
                    5 => -0.0009655265,
                    6 => -0.025747374,
                    7 => 0.0009908311,
                    8 => -0.017751137,
                    9 => -0.010210384,
                    10 => 0.0010643724,
                ],
            ],
            $result
        );
    }

    public function testEmbedQuery(): void
    {
        $openAI = $this->mockOpenAIWithResponses(
            [
                self::prepareResponse(
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

        $result = $openAI->embedQuery('foo bar baz');

        $this->assertEquals(
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
            $result
        );
    }

    private static function prepareResponse(array $response): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($response));
    }

    private static function mockOpenAIWithResponses(array $responses, array $options = []): OpenAIEmbeddings
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
}
