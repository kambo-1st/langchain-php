<?php

namespace Kambo\Langchain\VectorStores\SimpleStupidVectorStore;

use SQLite3;

use function array_map;
use function json_encode;
use function json_decode;
use function arsort;
use function array_slice;
use function sqrt;

use const SQLITE3_TEXT;
use const SQLITE3_INTEGER;
use const SQLITE3_FLOAT;
use const SQLITE3_ASSOC;

class Collection
{
    public function __construct(
        private int $collectionId,
        private SQLite3 $db,
        private array $options = [],
    ) {
    }

    public function add(
        array $metadatas,
        array $embeddings,
        iterable $texts,
        array $uuids
    ): array {
        // combine three arrays at row level e.g. $metadatas[0], $texts[0], $uuids[0] will in $result[0]
        $combined = array_map(null, $metadatas, $texts, $uuids, $embeddings);

        $embeddingsIds = [];
        foreach ($combined as $row) {
            $stmt = $this->db->prepare(
                'INSERT INTO embeddings (uuid, collection_id, document, metadata)
VALUES (:uuid, :collection_id, :document, :metadata)'
            );
            $stmt->bindValue(':uuid', $row[2], SQLITE3_TEXT);
            $stmt->bindValue(':collection_id', $this->collectionId, SQLITE3_INTEGER);
            $stmt->bindValue(':document', $row[1], SQLITE3_TEXT);
            $stmt->bindValue(':metadata', json_encode($row[0]), SQLITE3_TEXT);
            $stmt->execute();
            $embeddingsId = $this->db->lastInsertRowID();
            $embeddingsIds[] = $embeddingsId;

            foreach ($row[3] as $value) {
                $stmt = $this->db->prepare(
                    'INSERT INTO embeddings_values (embeddings_id, value) VALUES (:embeddings_id, :value)'
                );
                $stmt->bindValue(':embeddings_id', $embeddingsId, SQLITE3_INTEGER);
                $stmt->bindValue(':value', $value, SQLITE3_FLOAT);
                $stmt->execute();
            }
        }

        return $embeddingsIds;
    }

    public function similaritySearchWithScore(array $queryEmbedding, int $k)
    {
        $stmt = $this->db->prepare(
            'SELECT embeddings.id, embeddings.uuid, embeddings.document, embeddings.metadata, embeddings_values.value
            FROM embeddings
            INNER JOIN embeddings_values ON embeddings.id = embeddings_values.embeddings_id
            WHERE embeddings.collection_id = :collection_id
            ORDER BY embeddings.id ASC'
        );
        $stmt->bindValue(':collection_id', $this->collectionId, SQLITE3_INTEGER);
        $result = $stmt->execute();

        $embeddings = [];
        $embeddingsData = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $embeddings[$row['id']][] = $row['value'];
            $embeddingsData[$row['id']] = [
                'uuid' => $row['uuid'],
                'document' => $row['document'],
                'metadata' => json_decode($row['metadata'], true),
            ];
        }

        $scores = [];
        foreach ($embeddings as $id => $embedding) {
            $scores[$id] = $this->cosineSimilarity($queryEmbedding, $embedding);
        }

        arsort($scores);

        $result = [];
        foreach (array_slice($scores, 0, $k, true) as $id => $score) {
            $result[] = [
                $embeddingsData[$id],
                $score,
                /*'id' => $id,
                'score' => $score,*/
            ];
        }

        return $result;
    }

    private function cosineSimilarity(array $queryEmbedding, mixed $embedding)
    {
        $dotProduct = 0;
        $queryEmbeddingLength = 0;
        $embeddingLength = 0;

        foreach ($queryEmbedding as $key => $value) {
            $dotProduct += $value * $embedding[$key];
            $queryEmbeddingLength += $value * $value;
            $embeddingLength += $embedding[$key] * $embedding[$key];
        }

        return $dotProduct / (sqrt($queryEmbeddingLength) * sqrt($embeddingLength));
    }
}
