<?php

require __DIR__.'/../vendor/autoload.php';

//putenv("OPENAI_API_KEY=XXX"); // put our API key here

use Kambo\Langchain\DocumentLoaders\TextLoader;
use Kambo\Langchain\Indexes\VectorstoreIndexCreator;
use Kambo\Langchain\LLMs\OpenAI;

$loader = new TextLoader('billa_clanek.txt');
$index  = (new VectorstoreIndexCreator())->fromLoaders([$loader]);
$openAi = new OpenAI(['temperature' => 0]);

$query = "Co Billa spustila?";
echo "Otazka: " . $query . "\n";
echo $index->query($query, $openAi). "\n";

$query = "Kde to Billa spustila??";
echo "Otazka: " . $query . "\n";
echo $index->query($query, $openAi). "\n";

$query = "Kde Bator vedl Billu?";
echo "Otazka: " . $query . "\n";
echo $index->query($query, $openAi). "\n";
