<?php

namespace Kambo\Langchain\Tests\LLMs;

use PHPUnit\Framework\TestCase;
use Kambo\Langchain\LLMs\OpenAI;
use GuzzleHttp\Client as GuzzleClient;
use OpenAI\Client;
use OpenAI\Transporters\HttpTransporter;
use OpenAI\ValueObjects\ApiKey;
use OpenAI\ValueObjects\Transporter\BaseUri;
use OpenAI\ValueObjects\Transporter\Headers;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

use function json_encode;
use function array_merge;

class OpenAITest extends TestCase
{
    public function testExecute(): void
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

        $this->assertEquals(
            'Kaleidosocks',
            $openAI('What would be a good company name for a company that makes colorful socks?')
        );
    }

    public function testGenerate(): void
    {
        $openAI = $this->mockOpenAIWithResponses(
            [
                self::prepareResponse(
                    [
                        'id' => 'cmpl-6yFFzdFW5xyM4UDJqG5u3EcIWNXOW',
                        'object' => 'text_completion',
                        'created' => 1679816347,
                        'model' => 'text-davinci-003',
                        'choices' =>
                            [
                                [
                                    'text' => 'Q: What did the fish say when it swam into a wall? A: Dam!',
                                    'index' => 0,
                                    'logprobs' => null,
                                    'finish_reason' => 'stop',
                                ],
                                [
                                    'text' => 'Roses are red, Violets are blue, Sugar is sweet, And so are you!',
                                    'index' => 1,
                                    'logprobs' => null,
                                    'finish_reason' => 'stop',
                                ],
                            ],
                        'usage' => [
                            'prompt_tokens' => 8,
                            'completion_tokens' => 48,
                            'total_tokens' => 56,
                        ],
                    ]
                )
            ]
        );

        $result = $openAI->generate(['Tell me a joke', 'Tell me a poem']);

        $this->assertEquals(
            'Q: What did the fish say when it swam into a wall? A: Dam!',
            $result->getFirstGenerationText()
        );

        $answers = [];
        foreach ($result->getGenerations() as $generation) {
            foreach ($generation as $gen) {
                $answers[] = $gen->text;
            }
        }

        $this->assertEquals(
            [
                'Q: What did the fish say when it swam into a wall? A: Dam!',
                'Roses are red, Violets are blue, Sugar is sweet, And so are you!',
            ],
            $answers
        );

        $this->assertEquals(
            [
                'token_usage' => [
                    'completion_tokens' => 48,
                    'prompt_tokens' => 8,
                    'total_tokens' => 56,
                ],
                'model_name' => 'text-davinci-003',
            ],
            $result->getLLMOutput()
        );
    }

    private static function prepareResponse(array $response): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($response));
    }

    private static function mockOpenAIWithResponses(array $responses, array $options = []): OpenAI
    {
        $mock = new MockHandler($responses);

        $client = self::client($mock);
        return new OpenAI(array_merge(['openai_api_key' => 'test'], $options), $client);
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
