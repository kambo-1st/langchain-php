<?php

require __DIR__ . '/../vendor/autoload.php';

//putenv("OPENAI_API_KEY=XXX"); // put our API key here

use Kambo\Langchain\LLMs\OpenAI;
use Kambo\Langchain\Prompts\PromptTemplate;
use Kambo\Langchain\Chains\LLMChain;

$llm = new OpenAI(['temperature' => 0]);
$prompt = new PromptTemplate(
    "Question: {question}\nAnswer: Let's think step by step.?",
    ['question'],
);

$chain = new LLMChain($llm, $prompt, ['verbose' => true]);

$question = 'What NFL team won the Super Bowl in the year Justin Beiber was born?';

echo $chain->predict(['question' => $question]);
