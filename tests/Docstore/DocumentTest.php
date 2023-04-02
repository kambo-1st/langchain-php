<?php

namespace Kambo\Langchain\Tests\Docstore;

use PHPUnit\Framework\TestCase;
use Kambo\Langchain\Docstore\Document;

class DocumentTest extends TestCase
{
    private const PAGE_CONTENT = "This is a page about LangChain.

It is a really cool framework.

What isn't there to love about langchain?

Made in 2022.";

    public function testDocumentSummary(): void
    {
        $page = new Document(self::PAGE_CONTENT);
        $this->assertEquals('This is a page about LangChain.', $page->summary());
    }

    public function testDocumentLookup(): void
    {
        $page = new Document(self::PAGE_CONTENT);

        $output = $page->lookup('LangChain');
        $this->assertEquals('(Result 1/2) This is a page about LangChain.', $output);

        $output = $page->lookup('framework');
        $this->assertEquals('(Result 1/1) It is a really cool framework.', $output);

        $output = $page->lookup('LangChain');
        $this->assertEquals('(Result 1/2) This is a page about LangChain.', $output);

        $output = $page->lookup('LangChain');
        $this->assertEquals("(Result 2/2) What isn't there to love about langchain?", $output);
    }

    public function testDocumentLookupsDontExist(): void
    {
        $page = new Document(self::PAGE_CONTENT);

        $output = $page->lookup('harrison');
        $this->assertEquals('No Results', $output);
    }

    public function testDocumentLookupsTooMany(): void
    {
        $page = new Document(self::PAGE_CONTENT);

        $output = $page->lookup('framework');
        $this->assertEquals('(Result 1/1) It is a really cool framework.', $output);

        $output = $page->lookup('framework');
        $this->assertEquals('No More Results', $output);
    }
}
