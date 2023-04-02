<?php

namespace Kambo\Langchain\Prompts;

use Stringable;

interface PromptValue extends Stringable
{
    public function toMessages(): array;
    public function __toString(): string;
}
