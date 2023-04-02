<?php

namespace Kambo\Langchain\TextSplitter;

use Kambo\Langchain\Docstore\Document;
use Exception;

use function array_fill;
use function count;
use function array_map;
use function implode;
use function trim;
use function strlen;
use function error_log;
use function array_shift;
use function sprintf;

/**
 * Base class for splitting text into chunks.
 */
abstract class TextSplitter
{
    protected int $chunkSize;
    protected int $chunkOverlap;

    /**
     * Create a new TextSplitter.
     *
     * @param int $chunkSize
     * @param int $chunkOverlap
     */
    public function __construct(int $chunkSize = 1000, int $chunkOverlap = 200)
    {
        if ($chunkOverlap > $chunkSize) {
            throw new Exception(
                sprintf(
                    'Got a larger chunk overlap (%d) than chunk size (%d), should be smaller.',
                    $chunkOverlap,
                    $chunkSize
                )
            );
        }

        $this->chunkSize = $chunkSize;
        $this->chunkOverlap = $chunkOverlap;
    }

    /**
     * Split text into multiple components.
     *
     * @param string $text
     *
     * @return array
     */
    abstract public function splitText(string $text): array;

    /**
     * Create documents from a list of texts.
     *
     * @param array $texts
     * @param ?array $metadata
     *
     * @return array
     */
    public function createDocuments(array $texts, array $metadata = null): array
    {
        $metadata = $metadata ?? array_fill(0, count($texts), array());
        $documents = [];
        foreach ($texts as $i => $text) {
            foreach ($this->splitText($text) as $chunk) {
                $newDoc = new Document(pageContent:$chunk, metadata:$metadata[$i]);
                $documents[] = $newDoc;
            }
        }

        return $documents;
    }

    /**
     * Split documents
     *
     * @param array $documents
     *
     * @return array
     */
    public function splitDocuments(array $documents): array
    {
        $texts = array_map(function ($doc) {
            return $doc->pageContent;
        }, $documents);
        $metadatas = array_map(function ($doc) {
            return $doc->metadata;
        }, $documents);
        return $this->createDocuments($texts, $metadatas);
    }

    private function joinDocs($docs, $separator)
    {
        $text = implode($separator, $docs);
        $text = trim($text);
        if ($text === '') {
            return null;
        } else {
            return $text;
        }
    }

    /**
     * We now want to combine these smaller pieces into medium size
     * chunks to send to the LLM.
     *
     * @param iterable $splits
     * @param string   $separator
     *
     * @return array
     */
    protected function mergeSplits(iterable $splits, string $separator): array
    {
        $separatorLen = strlen($separator);

        $docs = [];
        $currentDoc = [];
        $total = 0;

        foreach ($splits as $d) {
            $len = strlen($d);
            if ($total + $len + (count($currentDoc) > 0 ? $separatorLen : 0) > $this->chunkSize) {
                if ($total > $this->chunkSize) {
                    error_log(
                        sprintf(
                            'Created a chunk of size %d, which is longer than the specified %d',
                            $total,
                            $this->chunkSize
                        )
                    );
                }

                if (count($currentDoc) > 0) {
                    $doc = $this->joinDocs($currentDoc, $separator);
                    if ($doc !== null) {
                        $docs[] = $doc;
                    }

                    while (
                        $total > $this->chunkOverlap
                        || (
                            $total + $len + (count($currentDoc) > 0 ? $separatorLen : 0) > $this->chunkSize
                            && $total > 0
                        )
                    ) {
                        $total -= strlen($currentDoc[0]) + (count($currentDoc) > 1 ? $separatorLen : 0);
                        array_shift($currentDoc);
                    }
                }
            }

            $currentDoc[] = $d;
            $total += $len + (count($currentDoc) > 1 ? $separatorLen : 0);
        }

        $doc = $this->joinDocs($currentDoc, $separator);
        if ($doc !== null) {
            $docs[] = $doc;
        }

        return $docs;
    }

    /**
     * @param mixed $s
     *
     * @return int
     */
    protected function lengthFunction(mixed $s)
    {
        return strlen($s);
    }
}
