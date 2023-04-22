<?php

require __DIR__.'/../vendor/autoload.php';

use Kambo\Langchain\LLMs\LLaMACpp;

$llm = new LLaMACpp(['model_path' => 'path/to/model.bin']);
$text = "What's the best programing language for web?";
echo $llm($text);
