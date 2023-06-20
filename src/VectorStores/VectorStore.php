<?php

namespace Kambo\Langchain\VectorStores;

use Exception;
use Kambo\Langchain\Embeddings\Embeddings;

use function array_column;

/**
 * Common parent for all vectorstores.
 */
abstract class VectorStore
{
    const SIMILARITY_SEARCH = 'similarity';
    const MAX_MARGINAL_RELEVANCE_SEARCH = 'mmr';

    /**
     * @param iterable $texts Iterable of strings to add to the vectorstore.
     * @param array|null $metadata Optional list of metadatas associated with the texts.
     * @param array $additionalArguments vectorstore specific parameters
     *
     * @return array List of ids from adding the texts into the vectorstore.
     */
    abstract public function addTexts(iterable $texts, ?array $metadata = null, array $additionalArguments = []): array;

    /**
     * Return docs most similar to query.
     *
     * @param string $query
     * @param int    $k
     * @param array $additionalArguments vectorstore specific parameters
     *
     * @return array
     */
    abstract public function similaritySearch(string $query, int $k = 4, array $additionalArguments = []): array;

    /**
     * Run more documents through the embeddings and add to the vectorstore.
     *
     * @param array $documents           Documents to add to the vectorstore.
     * @param array $additionalArguments vectorstore specific parameters
     *
     * @return array List of IDs of the added texts.
     */
    public function addDocuments(array $documents, array $additionalArguments = []): array
    {
        $texts = array_column($documents, 'pageContent');
        $metadatas = array_column($documents, 'metadata');

        return $this->addTexts($texts, $metadatas, $additionalArguments);
    }

    /**
     * Return docs most similar to embedding vector.
     *
     * @param array $embedding           Embedding to look up documents similar to.
     * @param int   $k                   Number of Documents to return. Defaults to 4.
     * @param array $additionalArguments vectorstore specific parameters
     *
     * @return array List of Documents most similar to the query vector.
     */
    public function similaritySearchByVector(array $embedding, int $k = 4, array $additionalArguments = []): array
    {
        throw new Exception('Method not implemented');
    }

    /**
     * Return docs selected using the maximal marginal relevance.
     * Maximal marginal relevance optimizes for similarity to query AND diversity among selected documents.
     *
     * @param string $query Text to look up documents similar to.
     * @param int    $k Number of Documents to return. Defaults to 4.
     * @param int    $fetchK Number of Documents to fetch to pass to MMR algorithm.
     *
     * @return array List of Documents selected by maximal marginal relevance.
     */
    public function maxMarginalRelevanceSearch(string $query, int $k = 4, int $fetchK = 20): array
    {
        throw new Exception('Method not implemented');
    }

    /**
     * Return docs selected using the maximal marginal relevance.
     *
     * Maximal marginal relevance optimizes for similarity to query AND diversity among selected documents.
     *
     * @param array $embedding Embedding to look up documents similar to.
     * @param int   $k Number of Documents to return. Defaults to 4.
     * @param int   $fetchK Number of Documents to fetch to pass to MMR algorithm.
     *
     * @return array List of Documents selected by maximal marginal relevance.
     */
    public function maxMarginalRelevanceSearchByVector(array $embedding, int $k = 4, int $fetchK = 20): array
    {
        throw new Exception('Method not implemented');
    }

    /**
     * Return VectorStore initialized from documents and embeddings.
     *
     * @param array      $documents
     * @param Embeddings $embedding
     * @param array      $additionalArguments vectorstore specific parameters
     *
     * @return VectorStore
     * @throws Exception
     */
    public static function fromDocuments(
        array $documents,
        Embeddings $embedding,
        array $additionalArguments = []
    ): VectorStore {
        $texts = array_column($documents, 'pageContent');
        $metadatas = array_column($documents, 'metadata');

        return static::fromTexts($texts, $embedding, $metadatas, $additionalArguments);
    }

    /**
     * Return VectorStore initialized from texts and embeddings.
     *
     * @param array      $texts
     * @param Embeddings $embedding
     * @param array|null $metadata
     * @param array      $additionalArguments vectorstore specific parameters
     *
     * @return VectorStore
     * @throws Exception
     */
    public static function fromTexts(
        array $texts,
        Embeddings $embedding,
        ?array $metadata = null,
        array $additionalArguments = []
    ): VectorStore {
        throw new Exception('Method not implemented');
    }
}
