<?php

require __DIR__.'/../vendor/autoload.php';

use Kambo\Langchain\Embeddings\LLaMACpp;

$embedding = new LLaMACpp(['model_path' => '/home/kambo/workspace/llama.cpp-php/examples/models/ggjt-model.bin']);
$text = "What's the best programing language for web?";
var_dump($embedding->embedQuery($text));
