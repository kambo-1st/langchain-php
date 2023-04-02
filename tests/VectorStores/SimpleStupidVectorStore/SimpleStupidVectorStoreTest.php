<?php

namespace Kambo\Langchain\Tests\VectorStores\SimpleStupidVectorStore;

use Kambo\Langchain\VectorStores\SimpleStupidVectorStore\Collection;
use Kambo\Langchain\VectorStores\SimpleStupidVectorStore\SimpleStupidVectorStore;
use PHPUnit\Framework\TestCase;
use SQLite3;
use ReflectionClass;

class SimpleStupidVectorStoreTest extends TestCase
{
    public function testConstructorCreatesTables()
    {
        $store = new SimpleStupidVectorStore();
        $reflection = new ReflectionClass($store);

        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $db = $dbProperty->getValue($store);

        $tables = ['collection', 'embeddings', 'embeddings_values'];

        foreach ($tables as $tableName) {
            $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='" . $tableName . "'");
            $this->assertNotNull($result->fetchArray(), 'Table ' . $tableName . ' should be created');
        }
    }

    public function testGetOrCreateCollection()
    {
        $store = new SimpleStupidVectorStore();
        $collectionName = 'test_collection';
        $options = ['option1' => 'value1'];

        $collection = $store->getOrCreateCollection($collectionName, $options);

        $this->assertInstanceOf(Collection::class, $collection);

        $reflection = new ReflectionClass($collection);
        $idProperty = $reflection->getProperty('collectionId');
        $idProperty->setAccessible(true);
        $collectionId = $idProperty->getValue($collection);

        $optionsProperty = $reflection->getProperty('options');
        $optionsProperty->setAccessible(true);
        $collectionOptions = $optionsProperty->getValue($collection);

        $this->assertSame($options, $collectionOptions);

        $secondCallCollection = $store->getOrCreateCollection($collectionName, $options);
        $secondCallCollectionId = $idProperty->getValue($secondCallCollection);

        $this->assertSame(
            $collectionId,
            $secondCallCollectionId,
            'Calling getOrCreateCollection with the same name should return the same collection'
        );
    }
}
