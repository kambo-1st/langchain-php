<?php

namespace Kambo\Langchain\Tests\VectorStores;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Kambo\Langchain\Embeddings\Embeddings;
use Kambo\Langchain\Embeddings\OpenAIEmbeddings;
use Kambo\Langchain\VectorStores\VectorStore;
use OpenAI\Client;
use OpenAI\Transporters\HttpTransporter;
use OpenAI\ValueObjects\ApiKey;
use OpenAI\ValueObjects\Transporter\BaseUri;
use OpenAI\ValueObjects\Transporter\Headers;
use PHPUnit\Framework\TestCase;
use Kambo\Langchain\Docstore\Document;

use function json_encode;
use function array_merge;

abstract class AbstractMockHandlerDrivenVectorStoreTestCase extends TestCase
{
    abstract function getVectorStore(Embeddings $embedding): VectorStore;

    public function testEmbedDocuments(): void
    {
        $openAI = $this->mockOpenAIEmbeddingsWithResponses(
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
                ),
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

        $SSVS = static::getVectorStore($openAI);
        $SSVS->addTexts(['foo bar baz'], []);

        $this->assertEquals(
            [
                new Document('foo bar baz'),
            ],
            $SSVS->similaritySearch('foo bar baz', 1)
        );
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
}
