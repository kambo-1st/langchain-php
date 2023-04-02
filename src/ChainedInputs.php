<?php

namespace Kambo\Langchain;

use function array_keys;
use function array_filter;
use function in_array;
use function count;

class ChainedInputs
{
    private const TEXT_COLOR_MAPPING = [
        'blue' => '36;1',
        'yellow' => '33;1',
        'pink' => '38;5;200',
        'green' => '32;1',
        'red' => '31;1'
    ];

    /**
     * Get mapping for items to a support color.
     *
     * @param array $items
     * @param array|null $excludedColors
     *
     * @return array
     */
    public static function getColorMapping(array $items, ?array $excludedColors = null): array
    {
        $colors = array_keys(self::TEXT_COLOR_MAPPING);
        if ($excludedColors !== null) {
            $colors = array_filter($colors, function ($c) use ($excludedColors) {
                return !in_array($c, $excludedColors);
            });
        }
        $colorMapping = [];
        $len = count($items);
        for ($i = 0; $i < $len; $i++) {
            $colorMapping[$items[$i]] = $colors[$i % count($colors)];
        }
        return $colorMapping;
    }

    /**
     * Get colored text.
     *
     * @param string $text
     * @param string $color
     *
     * @return string
     */
    public static function getColoredText(string $text, string $color): string
    {
        $colorStr = self::TEXT_COLOR_MAPPING[$color];
        return "\033[" . $colorStr . "m\033[1;3m" . $text . "\033[0m";
    }

    /**
     * Print text with highlighting and no end characters.
     *
     * @param string $text
     * @param string|null $color
     * @param string $end
     *
     * @return void
     */
    public static function printText(string $text, ?string $color = null, string $end = "\n"): void
    {
        if ($color === null) {
            echo $text;
        } else {
            echo self::getColoredText($text, $color);
        }
        echo $end;
    }
}
