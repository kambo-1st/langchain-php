<?php

namespace Kambo\Langchain\Tests\VectorStores;

use Kambo\Langchain\Embeddings\Embeddings;
use Kambo\Langchain\VectorStores\CachedSimpleStupidVectorStore;
use Kambo\Langchain\VectorStores\VectorStore;

class CachedSimpleStupidVectorStoreTest extends AbstractMockHandlerDrivenVectorStoreTestCase
{
    function getVectorStore(Embeddings $embedding): VectorStore
    {
        return new CachedSimpleStupidVectorStore($embedding);
    }
}
