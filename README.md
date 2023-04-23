# 🐘🦜️🔗 PHP LangChain

⚡ Building applications with LLMs through composability in PHP ⚡


[![Latest Version on Packagist](https://img.shields.io/packagist/v/kambo/langchain.svg?style=flat-square)](https://packagist.org/packages/kambo/langchain)
[![Tests](https://img.shields.io/github/actions/workflow/status/kambo-1st/langchain-php/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/kambo-1st/langchain-php/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/kambo/langchain.svg?style=flat-square)](https://packagist.org/packages/kambo/langchain)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](https://opensource.org/licenses/MIT)

The LangChain PHP Port is a meticulously crafted adaptation of the original [LangChain library](https://github.com/hwchase17/langchain), bringing its robust natural language processing capabilities to the PHP ecosystem. 
This faithful port allows developers to harness the full potential of LangChain's features, while preserving the familiar PHP syntax and structure.

## Installation

You can install the package via composer:

```bash
composer require kambo/langchain
```

## 🤔 What is this?

Large language models (LLMs) are emerging as a transformative technology, enabling
developers to build applications that they previously could not.
But using these LLMs in isolation is often not enough to
create a truly powerful app - the real power comes when you can combine them with other sources of computation or knowledge.

This library is aimed at assisting in the development of those types of applications. Common examples of these types of applications include:

## Work in progress

This library is still in development. Use at your own risk!

### Supported features

* Prompt formatting
* LLMs: OpenAI GPT-3, ChatGPT, llama.cpp
* Vector stores: Simple stupid vector store
* Texts splitters: CharacterTextSplitter, RecursiveCharacterTextSplitter
* Embeddings: OpenAI
* Document loaders: TextLoader

More to come!

### Documentation

* [PHP specific](docs/PHP/TOC.md)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
