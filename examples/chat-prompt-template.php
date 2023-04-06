<?php

require __DIR__.'/../vendor/autoload.php';

use Kambo\Langchain\Prompts\ChatPromptTemplate;
use Kambo\Langchain\Prompts\HumanMessagePromptTemplate;
use Kambo\Langchain\Prompts\SystemMessagePromptTemplate;
use Kambo\Langchain\Prompts\PromptTemplate;

$template="You are a helpful assistant that translates {input_language} to {output_language}.";
$systemMessagePrompt = SystemMessagePromptTemplate::fromTemplate($template);
$humanTemplate="{text}";
$humanMessagePrompt = HumanMessagePromptTemplate::fromTemplate($humanTemplate);

$chatPrompt = ChatPromptTemplate::fromMessages([$systemMessagePrompt, $humanMessagePrompt]);
$out = $chatPrompt->formatPrompt(
    [
        'input_language' => 'English',
        'output_language' => 'Spanish',
        'text'=>'I love programming.'
    ]
)->toMessages();
var_dump($out);


$prompt = new PromptTemplate(
    "You are a helpful assistant that translates {input_language} to {output_language}.",
    ["input_language", "output_language"],
);


$systemMessagePrompt = new SystemMessagePromptTemplate($prompt);
echo $systemMessagePrompt->format(['input_language' => 'English', 'output_language' => 'Spanish'])->formatChatML();

