<?php

namespace Kambo\Langchain\Indexes;

use Kambo\Langchain\TextSplitter\RecursiveCharacterTextSplitter;
use Kambo\Langchain\TextSplitter\TextSplitter;
use Kambo\Langchain\VectorStores\SimpleStupidVectorStore;
use Kambo\Langchain\Embeddings\Embeddings;
use Kambo\Langchain\Embeddings\OpenAIEmbeddings;

use function array_merge;

/**
 * Logic for creating indexes.
 */
class VectorstoreIndexCreator
{
    public string $vectorstoreCls = SimpleStupidVectorStore::class;
    public TextSplitter $textSplitter;
    public Embeddings $embedding;

    /**
     * @param string|null       $vectorstoreCls
     * @param Embeddings|null   $embedding
     * @param TextSplitter|null $textSplitter
     */
    public function __construct(
        ?string $vectorstoreCls = null,
        ?Embeddings $embedding = null,
        ?TextSplitter $textSplitter = null
    ) {
        $this->vectorstoreCls = $vectorstoreCls ?? SimpleStupidVectorStore::class;
        $this->textSplitter   = $textSplitter ?? new RecursiveCharacterTextSplitter(
            [
                'chunk_size' => 500,
                'chunk_overlap' => 0
            ]
        );
        $this->embedding = $embedding ?? new OpenAIEmbeddings();
    }

    /**
     * Create a vectorstore index from loaders.
     *
     * @param array $loaders
     * @param array $additionalParams
     *
     * @return VectorStoreIndexWrapper
     */
    public function fromLoaders(array $loaders, array $additionalParams = []): VectorStoreIndexWrapper
    {
        $docs = [];
        foreach ($loaders as $loader) {
            $docs = array_merge($docs, $loader->load());
        }

        $subDocs    = $this->textSplitter->splitDocuments($docs);
        $vectorstore = $this->vectorstoreCls::fromDocuments(
            $subDocs,
            $this->embedding,
            $additionalParams
        );

        return new VectorStoreIndexWrapper($vectorstore);
    }
}
