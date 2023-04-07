<?php

namespace Kambo\Langchain\Tests\Chains\CombineDocuments;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Kambo\Langchain\Chains\LLMChain;
use Kambo\Langchain\Docstore\Document;
use Kambo\Langchain\LLMs\OpenAI;
use Kambo\Langchain\Prompts\PromptTemplate;
use OpenAI\Client;
use OpenAI\Transporters\HttpTransporter;
use OpenAI\ValueObjects\ApiKey;
use OpenAI\ValueObjects\Transporter\BaseUri;
use OpenAI\ValueObjects\Transporter\Headers;
use PHPUnit\Framework\TestCase;
use Kambo\Langchain\Chains\CombineDocuments\StuffDocumentsChain;

use function json_encode;
use function array_merge;

class StuffDocumentsChainTest extends TestCase
{
    public function testLLMChainRun(): void
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
                ),
            ]
        );

        $prompt = new PromptTemplate(
            'What is a good name for a company that makes {product}?',
            ['product'],
        );

        $chain = new LLMChain($openAI, $prompt);

        $stuffDocumentsChain = new StuffDocumentsChain($chain);
        $result = $stuffDocumentsChain->combineDocs([new Document('colorful socks')]);
        $this->assertEquals(
            ['Kaleidosocks', []],
            $result
        );
    }

    public function testToArray(): void
    {
        $openAI = $this->mockOpenAIWithResponses();

        $prompt = new PromptTemplate(
            'What is a good name for a company that makes {product}?',
            ['product'],
        );

        $chain = new LLMChain($openAI, $prompt);

        $stuffDocumentsChain = new StuffDocumentsChain($chain);

        $this->assertEquals(
            [
                'memory' => null,
                'verbose' => false,
                'input_key' => 'input_documents',
                'output_key' => 'output_text',
                'document_variable_name' => 'product',
                'llm_chain' => [
                    'memory' => null,
                    'verbose' => false,
                    'llm' => [
                        'model_name' => 'text-davinci-003',
                        'model' => 'text-davinci-003',
                        'temperature' => 0.7,
                        'max_tokens' => 256,
                        'top_p' => 1,
                        'frequency_penalty' => 0,
                        'presence_penalty' => 0,
                        'n' => 1,
                        'best_of' => 1,
                        'logit_bias' => [],
                    ],
                    'prompt' => [
                        'input_variables' => [
                            'product',
                        ],
                        'template' => 'What is a good name for a company that makes {product}?',
                        'template_format' => 'f-string',
                        'validate_template' => true,
                        'type' => 'prompt',
                    ],
                    'output_key' => 'text',
                    '_type' => 'llm_chain',
                ],
                'document_prompt' => [
                    'input_variables' => [
                        'page_content',
                    ],
                    'template' => '{page_content}',
                    'template_format' => 'f-string',
                    'validate_template' => true,
                    'type' => 'prompt',
                ],
                '_type' => 'stuff_documents_chain',
            ],
            $stuffDocumentsChain->toArray()
        );
    }

    private static function prepareResponse(array $response): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($response));
    }

    private static function mockOpenAIWithResponses(array $responses = [], array $options = []): OpenAI
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
