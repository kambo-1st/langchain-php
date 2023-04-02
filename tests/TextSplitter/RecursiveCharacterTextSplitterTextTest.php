<?php

namespace Kambo\Langchain\Tests\TextSplitter;

use Kambo\Langchain\TextSplitter\RecursiveCharacterTextSplitter;
use PHPUnit\Framework\TestCase;

class RecursiveCharacterTextSplitterTextTest extends TestCase
{
    public function testIterativeTextSplitter(): void
    {
        $text = "Hi.\n\nI'm Harrison.\n\nHow? Are? You?\nOkay then f f f f.\nThis is a weird text to write, but gotta test the splittingggg some how.\n\nBye!\n\n-H.";
        $textSplitter = new RecursiveCharacterTextSplitter(['chunk_size' => 10, 'chunk_overlap' => 1]);
        $output = $textSplitter->splitText($text);

        $expectedOutput = [
            'Hi.',
            "I'm",
            'Harrison.',
            'How? Are?',
            'You?',
            'Okay then',
            'f f f f.',
            'This is a',
            'a weird',
            'text to',
            'write, but',
            'gotta test',
            'the',
            'splittingg',
            'ggg',
            'some how.',
            "Bye!\n\n-H."
        ];

        $this->assertEquals($expectedOutput, $output);
    }
}
