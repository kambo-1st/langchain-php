<?php

namespace Kambo\Langchain\Embeddings;

interface Embeddings
{
    public function embedDocuments(array $texts): array;
    public function embedQuery(string $text): array;
}
