<?php

namespace Kambo\Langchain\VectorStores;

use Kambo\Langchain\Embeddings\Embeddings;
use Kambo\Langchain\VectorStores\SimpleStupidVectorStore\SimpleStupidVectorStore as SSVStorage;
use Psr\Cache\CacheItemPoolInterface;

class CachedSimpleStupidVectorStore extends SimpleStupidVectorStore
{
    /** @var CacheItemPoolInterface */
    private $cacheItemPool;

    public function __construct(
        private Embeddings $embedding,
        SSVStorage $simpleStupidVectorStorage = null,
        $options = []
    ) {
        parent::__construct($embedding, $simpleStupidVectorStorage, $options);
        $this->cacheItemPool = $options['cacheItemPool'] ?? null;
    }

    public function addTexts(iterable $texts, ?array $metadata = null, array $additionalArguments = []): array
    {
        $textsHash = md5(serialize($texts));

        if ($this->cacheItemPool instanceof CacheItemPoolInterface) {
            $cachedItem = $this->cacheItemPool->getItem($textsHash);

            if ($cachedItem->isHit()) {
                $embeddings = $cachedItem->get();
            } else {
                $embeddings = $this->embedding->embedDocuments($texts);
                $this->cacheItemPool->save($cachedItem->set($embeddings));
            }

            return parent::addTexts($texts, $metadata, array_merge($additionalArguments, ['embeddings' => $embeddings]));
        }

        return parent::addTexts($texts, $metadata, $additionalArguments);
    }

    public function similaritySearch(string $query, int $k = 4, array $additionalArguments = []): array
    {
        $queryHash = md5(serialize($query));

        if ($this->cacheItemPool instanceof CacheItemPoolInterface) {
            $cachedItem = $this->cacheItemPool->getItem($queryHash);

            if ($cachedItem->isHit()) {
                $embeddings = $cachedItem->get();
            } else {
                $embeddings = $this->embedding->embedQuery($query);
                $this->cacheItemPool->save($cachedItem->set($embeddings));
            }

            parent::similaritySearch($query, $k, array_merge($additionalArguments, ['embeddings' => $embeddings]));
        }

        return parent::similaritySearch($query, $k, $additionalArguments);
    }
}
