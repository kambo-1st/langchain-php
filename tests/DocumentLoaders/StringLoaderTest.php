<?php

namespace Kambo\Langchain\Tests\DocumentLoaders;

use Kambo\Langchain\DocumentLoaders\BaseLoader;
use Kambo\Langchain\DocumentLoaders\StringLoader;

use SplFileInfo;
use const DIRECTORY_SEPARATOR;

class StringLoaderTest extends AbstractDocumentFromFixturesLoaderTestCase
{
    private $text;

    public function setUp(): void
    {
        parent::setUp();
        $filePath = new SplFileInfo(__DIR__ . DIRECTORY_SEPARATOR . 'fixtures.txt');
        $this->text = file_get_contents($filePath->getRealPath());
    }

    protected function getDocumentLoader(): BaseLoader
    {
        return new StringLoader($this->text);
    }
}
