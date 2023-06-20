<?php

namespace Kambo\Langchain\Tests\DocumentLoaders;

use Kambo\Langchain\DocumentLoaders\BaseLoader;
use Kambo\Langchain\DocumentLoaders\TextLoader;
use PHPUnit\Framework\TestCase;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

class TextLoaderTest extends AbstractDocumentFromFixturesLoaderTestCase
{
    protected function getDocumentLoader(): BaseLoader
    {
        return new TextLoader(__DIR__ . DIRECTORY_SEPARATOR . 'fixtures.txt');
    }
}
