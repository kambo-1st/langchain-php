<?php

namespace Kambo\Langchain\DocumentLoaders;

use Kambo\Langchain\Docstore\Document;
use SplFileInfo;

use function is_string;
use function file_get_contents;

/**
 * Load text files.
 */
final class TextLoader extends BaseLoader
{
    public SplFileInfo $filePath;

    /**
     * Initialize with file path.
     *
     * @param string|SplFileInfo $filePath
     */
    public function __construct(string|SplFileInfo $filePath)
    {
        if (is_string($filePath)) {
            $filePath = new SplFileInfo($filePath);
        }

        $this->filePath = $filePath;
    }

    /**
     * Load from file path.
     *
     * @return Document[]
     */
    public function load(): array
    {
        $text = file_get_contents($this->filePath->getRealPath());
        $metadata = ['source' => $this->filePath->getRealPath()];
        return [new Document(pageContent:$text, metadata: $metadata)];
    }
}
