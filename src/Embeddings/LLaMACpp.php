<?php

namespace Kambo\Langchain\Embeddings;

use Kambo\Langchain\Exceptions\InvalidArgumentException;
use Kambo\LLamaCPPLangchainAdapter\LLamaCPPLangchainAdapter;

use function class_exists;

/**
 * Embeddings implementation for LLaMACpp
 */
class LLaMACpp implements Embeddings
{
    public ?string $modelPath = null;

    public int $nCtx = 512;
    public int $nParts = -1;
    public int $seed = -1;
    public bool $f16Kv = false;
    public bool $logitsAll = false;
    public bool $vocabOnly = false;
    public bool $useMlock = false;
    public ?int $nThreads = 10;
    public int $nBatch = 8;
    private ?LLamaCPPLangchainAdapter $adapter;

    public function __construct(
        array $options = [],
        ?LLamaCPPLangchainAdapter $adapter = null,
    ) {
        if (!class_exists(LLamaCPPLangchainAdapter::class)) {
            throw new InvalidArgumentException(
                'Could not found LLamaCPPLangchainAdapter.
                Please install the LLamaCPPLangchainAdapter library to use this model.'
            );
        }

        $this->modelPath = $options['model_path'] ?? $this->modelPath;
        $this->nCtx = $options['n_ctx'] ?? $this->nCtx;
        $this->nParts = $options['n_parts'] ?? $this->nParts;
        $this->seed = $options['seed'] ?? $this->seed;
        $this->f16Kv = $options['f16_kv'] ?? $this->f16Kv;
        $this->logitsAll = $options['logits_all'] ?? $this->logitsAll;
        $this->vocabOnly = $options['vocab_only'] ?? $this->vocabOnly;
        $this->useMlock = $options['use_mlock'] ?? $this->useMlock;
        $this->nThreads = $options['n_threads'] ?? $this->nThreads;
        $this->nBatch = $options['n_batch'] ?? $this->nBatch;

        if ($adapter === null) {
            $adapter = LLamaCPPLangchainAdapter::create(
                [
                    'model_path' => $this->modelPath,
                    'n_ctx' => $this->nCtx,
                    'n_parts' => $this->nParts,
                    'seed' => $this->seed,
                    'f16_kv' => $this->f16Kv,
                    'logits_all' => $this->logitsAll,
                    'vocab_only' => $this->vocabOnly,
                    'use_mlock' => $this->useMlock,
                    'n_threads' => $this->nThreads,
                    'embedding' => true,
                ]
            );
        }

        $this->adapter = $adapter;
    }

    public function embedDocuments(array $texts): array
    {
        $embeddings = [];
        foreach ($texts as $text) {
            $embeddings[] = $this->adapter->embed($text);
        }

        return $embeddings;
    }

    public function embedQuery(string $text): array
    {
        return $this->adapter->embed($text);
    }
}
