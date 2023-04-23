# How to Use LLaMaCpp

To utilize the LLaMaCpp library in PHP, follow these additional steps.

## Installation

Install the LLaMaCpp adapter via Composer:

```bash
composer require kambo/llama-cpp-langchain-adapter
```

Obtain the model, for example, by using this command:
```bash
wget https://huggingface.co/LLukas22/gpt4all-lora-quantized-ggjt/resolve/main/ggjt-model.bin
```

## Usage

You can use it like a regular LLM:

```php
use Kambo\Langchain\LLMs\LLaMACpp;

$llm = new LLaMACpp(['model_path' => 'path/to/model.bin']);
$text = "What's the best programing language for web?";
echo $llm($text);
```
