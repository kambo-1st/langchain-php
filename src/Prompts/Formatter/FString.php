<?php

namespace Kambo\Langchain\Prompts\Formatter;

use function strtr;
use function count;
use function array_diff;
use function array_flip;
use function preg_match_all;
use function trim;
use function array_unique;

/**
 * Formater ("f-string") must implement https://peps.python.org/pep-3101/
 */
class FString
{
    /**
     * @param string $string
     * @param array  $values
     *
     * @return string
     */
    public function format(string $string, array $values = []): string
    {
        $transformed = [];
        foreach ($values as $key => $value) {
            $transformed['{' . $key . '}'] = $value;
        }

        $formatted = strtr($string, $transformed);

        return $formatted;
    }

    /**
     * @param string $string
     * @param array  $values
     *
     * @return bool
     */
    public function validate(string $string, array $values = []): bool
    {
        if (empty($string)) {
            return false;
        }

        $parsed = $this->parse($string);

        if (count($parsed) !== count($values)) {
            return false;
        }

        return empty(array_diff($parsed, array_flip($values)));
    }

    /**
     * @param string $string
     *
     * @return array
     */
    public function parse(string $string): array
    {
        $matches = [];
        preg_match_all('/\{[a-zA-Z0-9_]+\}/', $string, $matches);

        $variables = [];
        foreach ($matches[0] as $match) {
            $variables[] = trim($match, '{}');
        }

        return array_unique($variables);
    }
}
