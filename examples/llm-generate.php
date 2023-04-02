<?php

require __DIR__.'/../vendor/autoload.php';

//putenv("OPENAI_API_KEY=XXX"); // put our API key here

use Kambo\Langchain\LLMs\OpenAI;

$llm = new OpenAI(['temperature' => 0.9]);
$result = $llm->generate(["Tell me a joke", "Tell me a poem"]);
foreach ($result->getGenerations() as $generation) {
    foreach ($generation as $gen) {
        echo $gen->text."\n";
    }
}

var_export($result->getLLMOutput());
