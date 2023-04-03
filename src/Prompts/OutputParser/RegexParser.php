<?php

namespace Kambo\Langchain\Prompts\OutputParser;

use Kambo\Langchain\Exceptions\ValueError;

use function preg_match;
use function array_combine;
use function array_slice;
use function array_fill_keys;
use function array_merge;

/**
 * Output parser that uses a regular expression to parse the output.
 */
final class RegexParser extends BaseOutputParser
{
    public string $regex;
    public array $outputKeys;
    public ?string $defaultOutputKey;

    /**
     * RegexParser constructor.
     *
     * @param string $regex
     * @param array $outputKeys
     * @param string|null $defaultOutputKey
     */
    public function __construct(string $regex, array $outputKeys, ?string $defaultOutputKey = null)
    {
        $this->regex = $regex;
        $this->outputKeys = $outputKeys;
        $this->defaultOutputKey = $defaultOutputKey;
    }

    /**
     * Return the type key.
     *
     * @return string
     */
    public function getType()
    {
        return 'regex_parser';
    }

    /**
     * Parse the output of an LLM call.
     *
     * @param string $text
     * @return array
     */
    public function parse(string $text)
    {
        if (preg_match($this->regex, $text, $matches)) {
            return array_combine(
                $this->outputKeys,
                array_slice($matches, 1)
            );
        } else {
            if ($this->defaultOutputKey === null) {
                throw new ValueError('Could not parse output: ' . $text);
            } else {
                return array_fill_keys(
                    $this->outputKeys,
                    $this->defaultOutputKey
                );
            }
        }
    }

    /**
     * Return dictionary representation of output parser.
     *
     * @param array $additionalParameters Additional parameters to include in the output
     * @return array
     */
    public function toArray(array $additionalParameters = []): array
    {
        return array_merge(
            [
                'regex' => $this->regex,
                'output_keys' => $this->outputKeys,
                'default_output_key' => $this->defaultOutputKey,
                'type' => $this->getType(),
            ],
            $additionalParameters
        );
    }
}
