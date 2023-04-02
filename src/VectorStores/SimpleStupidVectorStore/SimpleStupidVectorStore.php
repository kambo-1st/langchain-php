<?php

namespace Kambo\Langchain\VectorStores\SimpleStupidVectorStore;

use SQLite3;

use function implode;

use const SQLITE3_TEXT;

class SimpleStupidVectorStore
{
    private SQLite3 $db;
    private array $options;
    private const TABLES = [
        'collection' => [
            'id' => 'INTEGER PRIMARY KEY',
            'name' => 'VARCHAR(255) NOT NULL',
        ],
        'embeddings' => [
            'id' => 'INTEGER PRIMARY KEY',
            'uuid' => 'VARCHAR',
            'collection_id' => 'INTEGER',
            'document' => 'VARCHAR NOT NULL',
            'metadata' => 'VARCHAR NOT NULL',
        ],
        'embeddings_values' => [
            'id' => 'INTEGER PRIMARY KEY',
            'embeddings_id' => 'INTEGER',
            'value' => 'DOUBLE',
        ],
    ];

    public function __construct(array $options = [])
    {
        $this->options = $options;
        $this->db = new SQLite3(':memory:');

        $this->createTables($this->db, self::TABLES);
    }

    public function getOrCreateCollection(string $name, array $options): Collection
    {
        $stmt = $this->db->prepare('SELECT id FROM collection WHERE name = :name');
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $result = $stmt->execute();
        $resultArray = $result->fetchArray();

        if ($resultArray === false) {
            $stmt = $this->db->prepare('INSERT INTO collection (name) VALUES (:name)');
            $stmt->bindValue(':name', $name, SQLITE3_TEXT);
            $stmt->execute();
            $collectionId = $this->db->lastInsertRowID();
        } else {
            $collectionId = $resultArray[0];
        }

        return new Collection($collectionId, $this->db, $options);
    }

    protected function createTables($db, $tables)
    {
        foreach ($tables as $tableName => $columns) {
            $columnDefinitions = [];
            foreach ($columns as $columnName => $columnType) {
                $columnDefinitions[] = $columnName . ' ' . $columnType;
            }

            $columnDefinitionsSql = implode(', ', $columnDefinitions);
            $sql = 'CREATE TABLE "' . $tableName . '" (' . $columnDefinitionsSql . ')';
            $db->exec($sql);
        }
    }
}
