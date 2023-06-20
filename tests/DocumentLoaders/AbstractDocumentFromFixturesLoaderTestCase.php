<?php

namespace Kambo\Langchain\Tests\DocumentLoaders;

use Kambo\Langchain\DocumentLoaders\BaseLoader;
use Kambo\Langchain\TextSplitter\RecursiveCharacterTextSplitter;
use PHPUnit\Framework\TestCase;

use const PHP_EOL;

abstract class AbstractDocumentFromFixturesLoaderTestCase extends TestCase
{
    abstract protected function getDocumentLoader(): BaseLoader;

    public function testLoad()
    {
        $documentLoader = static::getDocumentLoader();
        $this->assertEquals(
            'Obchodní řetězec Billa v pondělí v Česku spustil pilotní verzi svého e-shopu, '
            . 'dostupný je zatím v Praze, v Brně a v jejich blízkém okolí.' . PHP_EOL,
            $documentLoader->load()[0]->pageContent
        );
    }

    public function testLoadAndSplitDefault()
    {
        $documentLoader = static::getDocumentLoader();
        $this->assertEquals(
            'Obchodní řetězec Billa v pondělí v Česku spustil pilotní verzi svého e-shopu, '
            . 'dostupný je zatím v Praze, v Brně a v jejich blízkém okolí.',
            $documentLoader->loadAndSplit()[0]->pageContent
        );
    }

    public function testLoadAndSplit()
    {
        $textSplitter = new RecursiveCharacterTextSplitter(['chunk_size' => 60, 'chunk_overlap' => 1]);
        $documentLoader = static::getDocumentLoader();

        $documents = $documentLoader->loadAndSplit($textSplitter);
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
