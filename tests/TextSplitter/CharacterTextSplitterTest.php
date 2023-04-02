<?php

namespace Kambo\Langchain\Tests\TextSplitter;

use Kambo\Langchain\TextSplitter\CharacterTextSplitter;
use PHPUnit\Framework\TestCase;
use Kambo\Langchain\Docstore\Document;

use function ini_set;

class CharacterTextSplitterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        ini_set('error_log', '/dev/null');
    }

    public function testCharacterTextSplitter(): void
    {
        $text = 'foo bar baz 123';
        $splitter = new CharacterTextSplitter(['separator' => ' ', 'chunk_size' => 7, 'chunk_overlap' => 3]);
        $output = $splitter->splitText($text);
        $expectedOutput = ['foo bar', 'bar baz', 'baz 123'];
        $this->assertEquals($expectedOutput, $output);
    }

    public function testCharacterTextSplitterEmptyDoc(): void
    {
        $text = 'foo  bar';
        $splitter = new CharacterTextSplitter(['separator' => ' ', 'chunk_size' => 2, 'chunk_overlap' => 0]);
        $output = $splitter->splitText($text);
        $expectedOutput = ['foo', 'bar'];
        $this->assertEquals($expectedOutput, $output);
    }

    public function testCharacterTextSplitterSepartorEmptyDoc(): void
    {
        $text = 'f b';
        $splitter = new CharacterTextSplitter(['separator' => ' ', 'chunk_size' => 2, 'chunk_overlap' => 0]);
        $output = $splitter->splitText($text);
        $expectedOutput = ['f', 'b'];
        $this->assertEquals($expectedOutput, $output);
    }

    public function testCharacterTextSplitterLong(): void
    {
        $text = 'foo bar baz a a';
        $splitter = new CharacterTextSplitter(['separator' => ' ', 'chunk_size' => 3, 'chunk_overlap' => 1]);
        $output = $splitter->splitText($text);
        $expectedOutput = ['foo', 'bar', 'baz', 'a a'];
        $this->assertEquals($expectedOutput, $output);
    }

    public function testCharacterTextSplitterShortWordsFirst(): void
    {
        $text = 'a a foo bar baz';
        $splitter = new CharacterTextSplitter(['separator' => ' ', 'chunk_size' => 3, 'chunk_overlap' => 1]);
        $output = $splitter->splitText($text);
        $expectedOutput = ['a a', 'foo', 'bar', 'baz'];
        $this->assertEquals($expectedOutput, $output);
    }

    public function testCharacterTextSplitterLongerWords(): void
    {
        $text = 'foo bar baz 123';
        $splitter = new CharacterTextSplitter(['separator' => ' ', 'chunk_size' => 1, 'chunk_overlap' => 1]);
        $output = $splitter->splitText($text);
        $expectedOutput = ['foo', 'bar', 'baz', '123'];
        $this->assertEquals($expectedOutput, $output);
    }

    public function testCreateDocuments(): void
    {
        // Replace Document with the appropriate class in your project.
        $texts = ['foo bar', 'baz'];
        $splitter = new CharacterTextSplitter(['separator' => ' ', 'chunk_size' => 3, 'chunk_overlap' => 0]);
        $docs = $splitter->createDocuments($texts);
        $expectedDocs = [
            new Document(pageContent: 'foo'),
            new Document(pageContent: 'bar'),
            new Document(pageContent: 'baz'),
        ];
        $this->assertEquals($expectedDocs, $docs);
    }

    public function testCreateDocumentsWithMetadata(): void
    {
        // Replace Document with the appropriate class in your project.
        $texts = ['foo bar', 'baz'];
        $splitter = new CharacterTextSplitter(['separator' => ' ', 'chunk_size' => 3, 'chunk_overlap' => 0]);
        $docs = $splitter->createDocuments($texts, [['source' => '1'], ['source' => '2']]);
        $expectedDocs = [
            new Document(pageContent: 'foo', metadata: ['source' => '1']),
            new Document(pageContent: 'bar', metadata: ['source' => '1']),
            new Document(pageContent: 'baz', metadata: ['source' => '2']),
        ];
        $this->assertEquals($expectedDocs, $docs);
    }

    public function testMetadataNotShallow(): void
    {
        // Replace Document with the appropriate class in your project.
        $texts = ['foo bar'];
        $splitter = new CharacterTextSplitter(['separator' => ' ', 'chunk_size' => 3, 'chunk_overlap' => 0]);
        $docs = $splitter->createDocuments($texts, [['source' => '1']]);
        $expectedDocs = [
            new Document(pageContent: 'foo', metadata: ['source' => '1']),
            new Document(pageContent: 'bar', metadata: ['source' => '1']),
        ];
        $this->assertEquals($expectedDocs, $docs);

        $docs[0]->metadata['foo'] = 1;
        $this->assertEquals(['source' => '1', 'foo' => 1], $docs[0]->metadata);
        $this->assertEquals(['source' => '1'], $docs[1]->metadata);
    }
}
