<?php

namespace Kambo\Langchain\VectorStores;

use Kambo\Langchain\Embeddings\Embeddings;
use Kambo\Langchain\VectorStores\SimpleStupidVectorStore\SimpleStupidVectorStore as SSVStorage;
use Ramsey\Uuid\Uuid;
use Kambo\Langchain\Docstore\Document;

use function count;

class SimpleStupidVectorStore extends VectorStore
{
    private const LANGCHAIN_DEFAULT_COLLECTION_NAME = 'langchain';
    private ?SSVStorage $storage;
    private $collection;

    public function __construct(
        private Embeddings $embedding,
        SSVStorage $simpleStupidVectorStorage = null,
        $options = []
    ) {
        if ($simpleStupidVectorStorage === null) {
            $simpleStupidVectorStorage = new SSVStorage(['persistent' => false]);
        }

        $this->storage = $simpleStupidVectorStorage;
        $this->collection = $this->storage->getOrCreateCollection(
            $options['collection_name'] ?? self::LANGCHAIN_DEFAULT_COLLECTION_NAME,
            [
                'embeddings' => $embedding,
            ]
        );
    }

    public function addTexts(iterable $texts, ?array $metadata = null, array $additionalArguments = []): array
    {
        $embeddings = $this->embedding->embedDocuments($texts);

        $uuids = [];
        for ($i = 0; $i < count($texts); $i++) {
            $uuid    = Uuid::uuid1();
            $uuids[] = $uuid->toString();
        }

        $this->collection->add($metadata, $embeddings, $texts, $uuids);

        return $uuids;
    }

    public function similaritySearch(string $query, int $k = 4, array $additionalArguments = []): array
    {
        $embeddings = $this->embedding->embedQuery($query);
        $data = $this->collection->similaritySearchWithScore($embeddings, $k);

        $documents = [];
        foreach ($data as [$doc]) {
            $documents[] = new Document($doc['document'], '', 0, $doc['metadata'] ?? []);
        }

        return $documents;
    }

    public static function fromTexts(
        array $texts,
        Embeddings $embedding,
        ?array $metadata = null,
        array $additionalArguments = []
    ): VectorStore {
        $self = new self($embedding, null, $additionalArguments);

        $self->addTexts($texts, $metadata);
        return $self;
    }
}
