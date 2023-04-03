<?php

namespace Kambo\Langchain\Tests\Prompts\OutputParser;

use Kambo\Langchain\Exceptions\ValueError;
use Kambo\Langchain\Prompts\OutputParser\RegexParser;
use PHPUnit\Framework\TestCase;

class RegexParserTest extends TestCase
{
    public function testGetTypeReturnsCorrectType()
    {
        $regexParser = new RegexParser('', []);
        $this->assertEquals('regex_parser', $regexParser->getType());
    }

    public function testParseReturnsCorrectArrayWhenRegexMatches()
    {
        $regex = '/^The (cat|dog) chased the (mouse|rat)$/i';
        $outputKeys = ['animal', 'prey'];
        $defaultOutputKey = null;
        $regexParser = new RegexParser($regex, $outputKeys, $defaultOutputKey);

        $text = 'The cat chased the mouse';
        $expectedOutput = [
            'animal' => 'cat',
            'prey' => 'mouse'
        ];
        $this->assertEquals($expectedOutput, $regexParser->parse($text));
    }

    public function testParseThrowsValueErrorWhenRegexDoesNotMatchAndDefaultOutputKeyIsNull()
    {
        $regex = '/^The (cat|dog) chased the (mouse|rat)$/i';
        $outputKeys = ['animal', 'prey'];
        $defaultOutputKey = null;
        $regexParser = new RegexParser($regex, $outputKeys, $defaultOutputKey);

        $text = 'The bird flew away';
        $this->expectException(ValueError::class);
        $regexParser->parse($text);
    }

    public function testParseReturnsArrayWithDefaultOutputKeyWhenRegexDoesNotMatchAndDefaultOutputKeyIsNotNull()
    {
        $regex = '/^The (cat|dog) chased the (mouse|rat)$/i';
        $outputKeys = ['animal', 'prey'];
        $defaultOutputKey = 'unknown';
        $regexParser = new RegexParser($regex, $outputKeys, $defaultOutputKey);

        $text = 'The bird flew away';
        $expectedOutput = [
            'animal' => 'unknown',
            'prey' => 'unknown'
        ];
        $this->assertEquals($expectedOutput, $regexParser->parse($text));
    }

    public function testToArrayReturnsCorrectArray()
    {
        $regex = '/^The (cat|dog) chased the (mouse|rat)$/i';
        $outputKeys = ['animal', 'prey'];
        $defaultOutputKey = 'unknown';
        $regexParser = new RegexParser($regex, $outputKeys, $defaultOutputKey);

        $additionalParameters = [
            'language' => 'en',
            'version' => '1.0'
        ];
        $expectedOutput = [
            'regex' => $regex,
            'output_keys' => $outputKeys,
            'default_output_key' => $defaultOutputKey,
            'type' => 'regex_parser',
            'language' => 'en',
            'version' => '1.0'
        ];
        $this->assertEquals($expectedOutput, $regexParser->toArray($additionalParameters));
    }
}
