<?php

require __DIR__.'/../vendor/autoload.php';

//putenv("OPENAI_API_KEY=XXX"); // put our API key here

use Kambo\Langchain\LLMs\OpenAI;
use Kambo\Langchain\Prompts\PromptTemplate;
use Kambo\Langchain\Chains\LLMChain;
use Kambo\Langchain\Memory\ConversationBufferWindowMemory;

$template = <<<TEMPLATE
Assistant is a large language model trained by OpenAI.

Assistant is designed to be able to assist with a wide range of tasks, from answering simple questions to providing in-depth explanations and discussions on a wide range of topics. As a language model, Assistant is able to generate human-like text based on the input it receives, allowing it to engage in natural-sounding conversations and provide responses that are coherent and relevant to the topic at hand.

Assistant is constantly learning and improving, and its capabilities are constantly evolving. It is able to process and understand large amounts of text, and can use this knowledge to provide accurate and informative responses to a wide range of questions. Additionally, Assistant is able to generate its own text based on the input it receives, allowing it to engage in discussions and provide explanations and descriptions on a wide range of topics.

Overall, Assistant is a powerful tool that can help with a wide range of tasks and provide valuable insights and information on a wide range of topics. Whether you need help with a specific question or just want to have a conversation about a particular topic, Assistant is here to assist.

{history}
Human: {human_input}
Assistant:
TEMPLATE;

$prompt = new PromptTemplate(
    $template,
    ["history", "human_input"]
);

$chatgpt_chain = new LLMChain(
    new OpenAI(["temperature" => 0]),
    $prompt,
    [
        'verbose' => true,
        'memory' => new ConversationBufferWindowMemory(),
    ]
);

$output = $chatgpt_chain->predict(["human_input" => "I want you to act as a Linux terminal. I will type commands and you will reply with what the terminal should show. I want you to only reply with the terminal output inside one unique code block, and nothing else. Do not write explanations. Do not type commands unless I instruct you to do so. When I need to tell you something in English I will do so by putting text inside curly brackets {like this}. My first command is pwd."]);
echo $output . "\n";

$output = $chatgpt_chain->predict(["human_input" => "ls ~"]);
echo $output . "\n";

$output = $chatgpt_chain->predict(["human_input" => "cd ~"]);
echo $output . "\n";

$output = $chatgpt_chain->predict(["human_input" => "{Please make a file jokes.txt inside and put some jokes inside}"]);
echo $output . "\n";

$output = $chatgpt_chain->predict(["human_input" => 'echo -e "x=lambda y:y*5+3;print(\'Result:\' + str(x(6)))" > run.py && python3 run.py']);
echo $output . "\n";

$output = $chatgpt_chain->predict(["human_input" => 'echo -e "print(list(filter(lambda x: all(x%d for d in range(2,x)),range(2,3**10)))[:10])" > run.py && python3 run.py']);
echo $output . "\n";

$docker_input = 'echo -e "echo \'Hello from Docker" > entrypoint.sh && echo -e "FROM ubuntu:20.04\nCOPY entrypoint.sh entrypoint.sh\nENTRYPOINT [\"/bin/sh\",\"entrypoint.sh\"]">Dockerfile && docker build . -t my_docker_image && docker run -t my_docker_image';
$output = $chatgpt_chain->predict(["human_input" => $docker_input]);
echo $output . "\n";


$output = $chatgpt_chain->predict(["human_input" =>"nvidia-smi"]);
echo $output . "\n";

$output = $chatgpt_chain->predict(["human_input" => "ping bbc.com"]);
echo $output . "\n";

$output = $chatgpt_chain->predict(["human_input" =>'curl -fsSL "https://api.github.com/repos/pytorch/pytorch/releases/latest" | jq -r \'.tag_name\' | sed \'s/[^0-9\.\-]*//g']);
echo $output . "\n";

$output = $chatgpt_chain->predict(["human_input" =>"lynx https://www.deepmind.com/careers"]);
echo $output . "\n";

$output = $chatgpt_chain->predict(["human_input" =>"curl https://chat.openai.com/chat"]);
echo $output . "\n";

$output = $chatgpt_chain->predict(["human_input" => 'curl --header "Content-Type:application/json" --request POST --data \'{"message": "What is artificial intelligence?"}\' https://chat.openai.com/chat']);
echo $output . "\n";

$output = $chatgpt_chain->predict(["human_input" => 'curl --header "Content-Type:application/json" --request POST --data \'{"message": "I want you to act as a Linux terminal. I will type commands and you will reply with what the terminal should show. I want you to only reply with the terminal output inside one unique code block, and nothing else. Do not write explanations. Do not type commands unless I instruct you to do so. When I need to tell you something in English I will do so by putting text inside curly brackets {like this}. My first command is pwd."}\' https://chat.openai.com/chat']);
echo $output . "\n";
