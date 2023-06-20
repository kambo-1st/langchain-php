<?php

namespace Kambo\Langchain\Tests\VectorStores;

use Kambo\Langchain\Embeddings\Embeddings;
use Kambo\Langchain\VectorStores\VectorStore;
use Kambo\Langchain\VectorStores\SimpleStupidVectorStore;

class SimpleStupidVectorStoreTest extends AbstractMockHandlerDrivenVectorStoreTestCase
{
    function getVectorStore(Embeddings $embedding): VectorStore
    {
        return new SimpleStupidVectorStore($embedding);
    }
}
