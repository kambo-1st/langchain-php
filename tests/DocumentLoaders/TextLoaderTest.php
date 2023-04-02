<?php

namespace Kambo\Langchain\Tests\DocumentLoaders;

use Kambo\Langchain\DocumentLoaders\TextLoader;
use Kambo\Langchain\TextSplitter\RecursiveCharacterTextSplitter;
use PHPUnit\Framework\TestCase;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

class TextLoaderTest extends TestCase
{
    public function testLoad()
    {
        $textLoader = new TextLoader(__DIR__ . DIRECTORY_SEPARATOR . 'fixtures.txt');
        $this->assertEquals(
            'Obchodní řetězec Billa v pondělí v Česku spustil pilotní verzi svého e-shopu, '
            . 'dostupný je zatím v Praze, v Brně a v jejich blízkém okolí.' . PHP_EOL,
            $textLoader->load()[0]->pageContent
        );
    }

    public function testLoadAndSplitDefault()
    {
        $textLoader = new TextLoader(__DIR__ . DIRECTORY_SEPARATOR . 'fixtures.txt');
        $this->assertEquals(
            'Obchodní řetězec Billa v pondělí v Česku spustil pilotní verzi svého e-shopu, '
            . 'dostupný je zatím v Praze, v Brně a v jejich blízkém okolí.',
            $textLoader->loadAndSplit()[0]->pageContent
        );
    }

    public function testLoadAndSplit()
    {
        $textSplitter = new RecursiveCharacterTextSplitter(['chunk_size' => 60, 'chunk_overlap' => 1]);
        $textLoader = new TextLoader(__DIR__ . DIRECTORY_SEPARATOR . 'fixtures.txt');

        $documents = $textLoader->loadAndSplit($textSplitter);
        $this->assertCount(3, $documents);
        $this->assertEquals(
            'Obchodní řetězec Billa v pondělí v Česku spustil',
            $documents[0]->pageContent
        );

        $this->assertEquals(
            'pilotní verzi svého e-shopu, dostupný je zatím v Praze,',
            $documents[1]->pageContent
        );
    }
}
