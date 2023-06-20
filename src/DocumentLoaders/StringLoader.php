<?php

namespace Kambo\Langchain\DocumentLoaders;

use Kambo\Langchain\Docstore\Document;

/**
 * Load strings.
 */
final class StringLoader extends BaseLoader
{
    /**
     * @param string $text
     */
    public function __construct(private string $text)
    {
    }

    /**
     * @return Document[]
     */
    public function load(): array
    {
        $metadata = [];
        return [new Document(pageContent:$this->text, metadata: $metadata)];
    }
}
