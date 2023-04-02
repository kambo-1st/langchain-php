<?php

namespace Kambo\Langchain\TextSplitter;

use function end;
use function strpos;
use function explode;
use function str_split;
use function array_merge;

/**
 * Implementation of splitting text that looks at characters.
 * Recursively tries to split by different characters to find one
 * that works.
 */
final class RecursiveCharacterTextSplitter extends TextSplitter
{
    private array $separators = ["\n\n", "\n", ' ', ''];

    /**
     * Create a new TextSplitter.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct(
            $options['chunk_size'] ?? 1000,
            $options['chunk_overlap'] ?? 200
        );

        $this->separators = $options['separators'] ?? $this->separators;
    }

    /**
     * Split incoming text and return chunks.
     *
     * @param string $text
     *
     * @return array
     */
    public function splitText(string $text): array
    {
        $finalChunks = [];
        // Get appropriate separator to use
        $separator = end($this->separators);
        foreach ($this->separators as $_s) {
            if ($_s == '') {
                $separator = $_s;
                break;
            }

            if (strpos($text, $_s) !== false) {
                $separator = $_s;
                break;
            }
        }

        // Now that we have the separator, split the text
        if ($separator) {
            $splits = explode($separator, $text);
        } else {
            $splits = str_split($text);
        }

        // Now go merging things, recursively splitting longer texts.
        $_goodSplits = [];
        foreach ($splits as $s) {
            if ($this->lengthFunction($s) < $this->chunkSize) {
                $_goodSplits[] = $s;
            } else {
                if ($_goodSplits) {
                    $mergedText = $this->mergeSplits($_goodSplits, $separator);
                    $finalChunks = array_merge($finalChunks, $mergedText);
                    $_goodSplits = [];
                }

                $otherInfo = $this->splitText($s);
                $finalChunks = array_merge($finalChunks, $otherInfo);
            }
        }

        if ($_goodSplits) {
            $mergedText = $this->mergeSplits($_goodSplits, $separator);
            $finalChunks = array_merge($finalChunks, $mergedText);
        }

        return $finalChunks;
    }
}
