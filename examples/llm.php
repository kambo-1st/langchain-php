<?php

require __DIR__.'/../vendor/autoload.php';

//putenv("OPENAI_API_KEY=XXX"); // put our API key here

use Kambo\Langchain\LLMs\OpenAI;

$llm = new OpenAI(['temperature' => 0.9]);
$text = "What would be a good company name for a company that makes colorful socks?";
echo $llm($text);
