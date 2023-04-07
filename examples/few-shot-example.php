<?php

require __DIR__ . '/../vendor/autoload.php';

use Kambo\Langchain\LLMs\OpenAI;
use Kambo\Langchain\Prompts\PromptTemplate;
use Kambo\Langchain\Chains\LLMChain;
use Kambo\Langchain\Prompts\FewShotPromptTemplate;

$examples = [
    [
        'query' => 'How are you?',
        'answer' => "I can't complain but sometimes I still do."
    ],
    [
        'query' => 'What time is it?',
        'answer' => "It's time to get a watch."
    ]
];

$exampleTemplate = '
User: {query}
AI: {answer}
';

$examplePrompt = new PromptTemplate(
    $exampleTemplate,
    ['query', 'answer'],
);

$prefix = "The following are excerpts from conversations with an AI
assistant. The assistant is typically sarcastic and witty, producing
creative  and funny responses to the users' questions. Here are some
examples:
";

$suffix = '
User: {query}
AI: ';

// now create the few-shot prompt template
$fewShotPromptTemplate = new FewShotPromptTemplate(
    prefix: $prefix,
    suffix: $suffix,
    examplePrompt: $examplePrompt,
    inputVariables: ['query'],
    settings: ['example_separator' => '\n\n'],
    examples: $examples,
);


$llm = new OpenAI(['temperature' => 0.9]);

$chain = new LLMChain($llm, $fewShotPromptTemplate);

echo 'few shots: ' . $chain->run('How do birds fly?') . PHP_EOL;

$plain = new PromptTemplate(
    '{query}',
    ['query'],
);
$chain = new LLMChain($llm, $plain);
echo 'normal: ' . $chain->run('How do birds fly?') . PHP_EOL;
