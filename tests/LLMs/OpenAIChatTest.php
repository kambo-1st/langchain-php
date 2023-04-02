<?php

namespace Kambo\Langchain\Tests\LLMs;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Kambo\Langchain\LLMs\OpenAIChat;
use OpenAI\Client;
use OpenAI\Transporters\HttpTransporter;
use OpenAI\ValueObjects\ApiKey;
use OpenAI\ValueObjects\Transporter\BaseUri;
use OpenAI\ValueObjects\Transporter\Headers;
use PHPUnit\Framework\TestCase;

use function json_encode;
use function array_merge;

class OpenAIChatTest extends TestCase
{
    public function testExecute(): void
    {
        $openAI = $this->mockOpenAIWithResponses(
            [
                self::prepareResponse(
                    [
                        'id' => 'chatcmpl-6yGpmeZ6v6cALFWagesgA9zvaYNTs',
                        'object' => 'chat.completion',
                        'created' => 1679822410,
                        'model' => 'gpt-3.5-turbo-0301',
                        'choices' =>
                            [
                                0 =>
                                    [
                                        'index' => 0,
                                        'message' =>
                                            [
                                                'role' => 'assistant',
                                                'content' => 'Happy Feet Co.',
                                            ],
                                        'finish_reason' => 'stop',
                                    ],
                            ],
                        'usage' =>
                            [
                                'prompt_tokens' => 23,
                                'completion_tokens' => 4,
                                'total_tokens' => 27,
                            ],
                    ]
                )
            ]
        );

        $this->assertEquals(
            'Happy Feet Co.',
            $openAI('What would be a good company name for a company that makes colorful socks?')
        );
    }

    public function testGenerate(): void
    {
        $openAI = $this->mockOpenAIWithResponses(
            [
                self::prepareResponse(
                    [
                        'id' => 'chatcmpl-6yGpmeZ6v6cALFWagesgA9zvaYNTs',
                        'object' => 'chat.completion',
                        'created' => 1679822410,
                        'model' => 'gpt-3.5-turbo-0301',
                        'choices' =>
                            [
                                0 =>
                                    [
                                        'index' => 0,
                                        'message' =>
                                            [
                                                'role' => 'assistant',
                                                'content' => 'Happy Feet Co.',
                                            ],
                                        'finish_reason' => 'stop',
                                    ],
                            ],
                        'usage' =>
                            [
                                'prompt_tokens' => 23,
                                'completion_tokens' => 4,
                                'total_tokens' => 27,
                            ],
                    ]
                )
            ]
        );

        $result = $openAI->generate(['Tell me a joke']);

        $this->assertEquals(
            'Happy Feet Co.',
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
                'Happy Feet Co.',
            ],
            $answers
        );

        $this->assertEquals(
            [
                'token_usage' => [
                    'prompt_tokens' => 23,
                    'completion_tokens' => 4,
                    'total_tokens' => 27,
                ],
            ],
            $result->getLLMOutput()
        );
    }

    private static function prepareResponse(array $response): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($response));
    }

    private static function mockOpenAIWithResponses(array $responses, array $options = []): OpenAIChat
    {
        $mock = new MockHandler($responses);

        $client = self::client($mock);
        return new OpenAIChat(array_merge(['openai_api_key' => 'test'], $options), $client);
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
