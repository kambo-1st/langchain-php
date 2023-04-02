<?php

require __DIR__ . '/../vendor/autoload.php';

//putenv("OPENAI_API_KEY=XXX"); // put our API key here

use Kambo\Langchain\LLMs\OpenAI;
use Kambo\Langchain\Prompts\PromptTemplate;
use Kambo\Langchain\Chains\LLMChain;

$llm = new OpenAI(['temperature' => 0.9]);
$prompt = new PromptTemplate(
    'What is a good name for a company that makes {product}?',
    ['product'],
);

$chain = new LLMChain($llm, $prompt, ['verbose' => true]);

echo $chain->run('colorful socks');
