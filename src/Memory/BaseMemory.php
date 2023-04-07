<?php

namespace Kambo\Langchain\Memory;

interface BaseMemory
{
    public function getMemoryVariables();

    public function loadMemoryVariables(array $inputs = []): array;

    public function saveContext(array $inputs, array $outputs): void;

    public function clear();

    public function toArray(): array;
}
