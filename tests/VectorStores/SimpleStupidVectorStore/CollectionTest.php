<?php

namespace Kambo\Langchain\Tests\VectorStores\SimpleStupidVectorStore;

use PHPUnit\Framework\TestCase;
use Kambo\Langchain\VectorStores\SimpleStupidVectorStore\Collection;
use SQLite3;

class CollectionTest extends TestCase
{
    private SQLite3 $db;
    private Collection $collection;

    protected function setUp(): void
    {
        $this->db = new SQLite3(':memory:');
        $this->db->exec(
            'CREATE TABLE embeddings (id INTEGER PRIMARY KEY, uuid TEXT,
collection_id INTEGER, document TEXT, metadata TEXT)'
        );
        $this->db->exec(
            'CREATE TABLE embeddings_values (id INTEGER PRIMARY KEY, embeddings_id INTEGER, value REAL)'
        );
        $this->collection = new Collection(1, $this->db);
    }

    public function testAdd(): void
    {
        $metadatas = [['title' => 'Title 1'], ['title' => 'Title 2']];
        $embeddings = [[0.1, 0.2], [0.3, 0.4]];
        $texts = ['Text 1', 'Text 2'];
        $uuids = ['uuid1', 'uuid2'];

        $embeddingsIds = $this->collection->add($metadatas, $embeddings, $texts, $uuids);

        $this->assertCount(2, $embeddingsIds);
    }

    public function testSimilaritySearchWithScore(): void
    {
        $metadatas = [['title' => 'Title 1'], ['title' => 'Title 2']];
        $embeddings = [[0.1, 0.2], [0.3, 0.4]];
        $texts = ['Text 1', 'Text 2'];
        $uuids = ['uuid1', 'uuid2'];

        $this->collection->add($metadatas, $embeddings, $texts, $uuids);

        $queryEmbedding = [0.15, 0.25];
        $k = 1;

        $results = $this->collection->similaritySearchWithScore($queryEmbedding, $k);

        $this->assertCount(1, $results);
        $this->assertSame('uuid1', $results[0][0]['uuid']);
        $this->assertSame('Text 1', $results[0][0]['document']);
        $this->assertSame(['title' => 'Title 1'], $results[0][0]['metadata']);
    }
}
