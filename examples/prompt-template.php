<?php
require __DIR__.'/../vendor/autoload.php';

use Kambo\Langchain\Prompts\PromptTemplate;

$prompt = new PromptTemplate(
    "What is a good name for a company that makes {product}?",
    ["product"],
);

echo $prompt->format(['product'=>"colorful socks"]);
