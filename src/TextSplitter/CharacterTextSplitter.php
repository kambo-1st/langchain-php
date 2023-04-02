<?php

namespace Kambo\Langchain\TextSplitter;

use function explode;
use function str_split;

/**
 * Implementation of splitting text that looks at characters.
 */
final class CharacterTextSplitter extends TextSplitter
{
    private string $separator = "\n\n";

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

        $this->separator = $options['separator'] ?? $this->separator;
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
        // First we naively split the large input into a bunch of smaller ones.
        if ($this->separator) {
            $splits = explode($this->separator, $text);
        } else {
            $splits = str_split($text);
        }

        return $this->mergeSplits($splits, $this->separator);
    }
}
