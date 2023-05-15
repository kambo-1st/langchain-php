<?php

require __DIR__.'/../vendor/autoload.php';

use Kambo\Langchain\LLMs\HuggingFaceHub;

$llm = new HuggingFaceHub();
$text = "The goal of life is?";
echo $llm($text);
