<?php

namespace Kambo\Langchain\DocumentLoaders;

use Kambo\Langchain\Docstore\Document;
use Kambo\Langchain\TextSplitter\RecursiveCharacterTextSplitter;
use Kambo\Langchain\TextSplitter\TextSplitter;

use function is_null;

/**
 * Base loader class.
 */
abstract class BaseLoader
{
    /**
     * Load data into document objects.
     *
     * @return Document[] Array of document objects.
     */
    abstract public function load(): array;

    /**
     * Load documents and split into chunks.
     *
     * @param ?TextSplitter $textSplitter Text splitter to use.
     *
     * @return array
     */
    public function loadAndSplit(?TextSplitter $textSplitter = null): array
    {
        if (is_null($textSplitter)) {
            $textSplitter = new RecursiveCharacterTextSplitter();
        }

        $docs = $this->load();
        return $textSplitter->splitDocuments($docs);
    }
}
