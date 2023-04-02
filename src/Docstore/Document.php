<?php

namespace Kambo\Langchain\Docstore;

use function explode;
use function strtolower;
use function array_values;
use function array_filter;
use function strpos;
use function count;

use const PHP_EOL;

/**
 * Interface for interacting with a document.
 */
class Document
{
    public string $pageContent;
    public string $lookupStr = '';
    public int $lookupIndex = 0;
    public array $metadata = [];

    /**
     * @param string $pageContent
     * @param string $lookupStr
     * @param int    $lookupIndex
     * @param array  $metadata
     */
    public function __construct(
        string $pageContent,
        string $lookupStr = '',
        int $lookupIndex = 0,
        array $metadata = []
    ) {
        $this->pageContent = $pageContent;
        $this->lookupStr = $lookupStr;
        $this->lookupIndex = $lookupIndex;
        $this->metadata = $metadata;
    }

    /**
     * Paragraphs of the page.
     *
     * @return array
     */
    public function paragraphs(): array
    {
        return explode(PHP_EOL . PHP_EOL, $this->pageContent);
    }

    /**
     * Summary of the page (the first paragraph).
     *
     * @return string
     */
    public function summary(): string
    {
        $paragraphs = $this->paragraphs();
        return $paragraphs[0];
    }

    /**
     * Lookup a term in the page, imitating cmd-F functionality.
     *
     * @param string $string
     *
     * @return string
     */
    public function lookup(string $string): string
    {
        if (strtolower($string) !== $this->lookupStr) {
            $this->lookupStr = strtolower($string);
            $this->lookupIndex = 0;
        } else {
            $this->lookupIndex++;
        }

        $lookups = array_values(array_filter($this->paragraphs(), function ($p) {
            return strpos(strtolower($p), $this->lookupStr) !== false;
        }));

        if (empty($lookups)) {
            return 'No Results';
        } elseif ($this->lookupIndex >= count($lookups)) {
            return 'No More Results';
        } else {
            $resultPrefix = '(Result ' . ($this->lookupIndex + 1) . '/' . count($lookups) . ')';
            return $resultPrefix . ' ' . $lookups[$this->lookupIndex];
        }
    }
}
