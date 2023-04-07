<?php

namespace Kambo\Langchain\Chains\VectorDbQa;

use Kambo\Langchain\Chains\Chain;
use Kambo\Langchain\VectorStores\VectorStore;
use Kambo\Langchain\LLMs\BaseLanguageModel;
use Kambo\Langchain\LLMs\BaseLLM;
use Kambo\Langchain\Chains\LLMChain;
use Kambo\Langchain\Callbacks\BaseCallbackManager;
use Kambo\Langchain\Prompts\BasePromptTemplate;
use Kambo\Langchain\Prompts\PromptTemplate;
use Kambo\Langchain\Chains\CombineDocuments\StuffDocumentsChain;
use Kambo\Langchain\Chains\CombineDocuments\BaseCombineDocumentsChain;
use Exception;

use function in_array;
use function array_merge;
use function implode;
use function array_keys;
use function call_user_func;

/**
 * Chain for question-answering against a vector database.
 */
class VectorDBQA extends Chain
{
    public VectorStore $vectorstore;
    public int $k = 4;
    public BaseCombineDocumentsChain $combineDocumentsChain;
    public string $inputKey = 'query';
    public string $outputKey = 'result';
    public bool $returnSourceDocuments = false;
    public array $searchKwargs;
    public string $searchType = 'similarity';

    public function __construct($params)
    {
        parent::__construct(null, null, null, $params);
        $this->vectorstore = $params['vectorstore'];
        $this->combineDocumentsChain = $params['combine_documents_chain'];
        $this->searchKwargs = $params['search_kwargs'] ?? [];
        $this->validateSearchType();
    }

    /**
     * Return the input keys.
     *
     * @return string[]
     */
    public function inputKeys(): array
    {
        return [$this->inputKey];
    }

    /**
     * Return the output keys.
     *
     * @return string[]
     */
    public function outputKeys(): array
    {
        $outputKeys = [$this->outputKey];
        if ($this->returnSourceDocuments) {
            $outputKeys[] = 'source_documents';
        }
        return $outputKeys;
    }

    /**
     * Validate search type.
     *
     * @return void
     */
    private function validateSearchType(): void
    {
        if (!in_array($this->searchType, ['similarity', 'mmr'])) {
            throw new Exception('search_type of ' . $this->searchType . ' not allowed.');
        }
    }

    /**
     * @param BaseLLM             $llm
     * @param PromptTemplate|null $prompt
     * @param array               $additionalArguments
     *
     * @return VectorDBQA
     */
    public static function fromLLM(
        BaseLLM $llm,
        PromptTemplate $prompt = null,
        array $additionalArguments = []
    ): VectorDBQA {
        $documentPrompt = new PromptTemplate(
            "Context:\n{page_content}",
            ['page_content'],
        );
        $llmChain = new LLMChain($llm, $prompt);
        $combineDocumentsChain = new StuffDocumentsChain(
            $llmChain,
            $documentPrompt,
            'context',
            $additionalArguments,
        );

        return new VectorDBQA(
            array_merge(['combine_documents_chain' => $combineDocumentsChain], $additionalArguments)
        );
    }

    /**
     * Load chain from chain type.
     *
     * @param BaseLLM    $llm
     * @param string     $chainType
     * @param array|null $chainType_kwargs
     * @param array      $kwargs
     *
     * @return VectorDBQA
     * @throws Exception
     */
    public static function fromChainType(
        BaseLLM $llm,
        string $chainType = 'stuff',
        ?array $chainType_kwargs = null,
        array $kwargs = []
    ): VectorDBQA {
        $chainType_kwargs = $chainType_kwargs ?? [];
        $combineDocuments_chain = self::loadQAChain(
            $llm,
            $chainType,
            null,
            null,
            $chainType_kwargs
        );
        return new VectorDBQA(array_merge(['combine_documents_chain' => $combineDocuments_chain], $kwargs));
    }

    /**
     * Load question answering chain.
     *
     * @param BaseLanguageModel $llm Language Model to use in the chain.
     * @param string $chainType Type of document combining chain to use. Should be one of "stuff",
     *                           "map_reduce", and "refine".
     * @param bool|null $verbose Whether chains should be run in verbose mode or not. Note that this
     *                           applies to all chains that make up the final chain.
     * @param BaseCallbackManager|null $callbackManager  Callback manager to use for the chain.
     * @param array|null $kwargs Other params to pass to the chain.
     *
     * @return BaseCombineDocumentsChain
     */
    public static function loadQAChain(
        BaseLanguageModel $llm,
        string $chainType = 'stuff',
        ?bool $verbose = null,
        ?BaseCallbackManager $callbackManager = null,
        ?array $kwargs = []
    ): BaseCombineDocumentsChain {
        return match ($chainType) {
            'stuff' => self::loadStuffChain(
                $llm,
                null,
                'context',
                $verbose,
                $callbackManager,
                $kwargs
            ),
            'map_reduce' => '_load_map_reduce_chain',
            'refine' => '_load_refine_chain',
            'map_rerank' => '_load_map_rerank_chain',
            default => throw new Exception(
                'Got unsupported chain type: '
                . $chainType
                . '. Should be one of: stuff, map_reduce, refine, map_rerank.'
            )
        };
    }

    /*public static function _load_stuff_chain_summary(
        BaseLLM $llm,
        BasePromptTemplate $prompt = null,
        string $documentVariable_name = "text",
        bool $verbose = null,
        ?array $kwargs = []
    ) {
        if ($prompt === null) {
            $promptTemplate = 'Write a concise summary of the following:' . PHP_EOL . PHP_EOL
             . '"{text}"' . PHP_EOL . PHP_EOL . 'CONCISE SUMMARY:';
            $prompt = new PromptTemplate([['template' => $promptTemplate, 'input_variables' => 'text']]);
        }

        $llmChain = new LLMChain($llm, $prompt, ['verbose' => $verbose]);

        $stuffDocuments_chain_args = array_merge([
            "llm_chain" => $llmChain,
            "document_variable_name" => $documentVariable_name,
            "verbose" => $verbose
        ], $kwargs ?? []);

        return new StuffDocumentsChain($llmChain, null, $documentVariable_name, $stuffDocuments_chain_args);
    }*/

    /**
     * @param BaseLanguageModel        $llm
     * @param BasePromptTemplate|null  $prompt
     * @param string                   $documentVariableName
     * @param bool|null                $verbose
     * @param BaseCallbackManager|null $callbackManager
     * @param array                    $kwargs
     *
     * @return StuffDocumentsChain
     */
    public static function loadStuffChain(
        BaseLanguageModel $llm,
        BasePromptTemplate $prompt = null,
        string $documentVariableName = 'context',
        bool $verbose = null,
        BaseCallbackManager $callbackManager = null,
        array $kwargs = []
    ) {
        if ($prompt === null) {
            $promptTemplate = "Use the following pieces of context to answer the question at the end.
            If you don't know the answer, just say that you don't know, don't try to make up an answer
            .\n\n{context}\n\nQuestion: {question}\nHelpful Answer:";
            $prompt = new PromptTemplate(
                $promptTemplate,
                ['context', 'question'],
            );
        }

        $llmChain = new LLMChain($llm, $prompt, ['verbose' => $verbose, 'callback_manager' => $callbackManager]);
        return new StuffDocumentsChain(
            $llmChain,
            null,
            $documentVariableName,
            array_merge([
                'verbose' => $verbose,
                'callback_manager' => $callbackManager
            ], $kwargs ?? []),
        );
    }

    /**
     * Run similarity search and llm on input query.
     *
     * If chain has 'return_source_documents' as 'True', returns
     * the retrieved documents as well under the key 'source_documents'.
     *
     * @param array $inputs
     *
     * @return array
     */
    protected function call(array $inputs): array
    {
        $question = $inputs[$this->inputKey];

        if ($this->searchType === 'similarity') {
            $docs = $this->vectorstore->similaritySearch($question, $this->k, $this->searchKwargs);
        } elseif ($this->searchType === 'mmr') {
            $docs = $this->vectorstore->maxMarginalRelevanceSearch($question, $this->k, $this->searchKwargs);
        } else {
            throw new Exception('search_type of ' . $this->searchType . ' not allowed.');
        }

        [$answer, $_] = $this->combineDocumentsChain->combineDocs($docs, ['question' => $question]);

        if ($this->returnSourceDocuments) {
            return [$this->outputKey => $answer, 'source_documents' => $docs];
        } else {
            return [$this->outputKey => $answer];
        }
    }

    public function getChainType(): string
    {
        return 'vector_db_qa';
    }

    public function toArray(): array
    {
        return [
            'memory' => $this->memory?->toArray(),
            'verbose' => $this->verbose,
            'k' => $this->k,
            'combine_documents_chain' => $this->combineDocumentsChain->toArray(),
            'search_type' => $this->searchType,
            'search_kwargs' => $this->searchKwargs,
            'return_source_documents' => $this->returnSourceDocuments,
            'input_key' => $this->inputKey,
            'output_key' => $this->outputKey,
            '_type' => $this->getChainType(),
        ];
    }
}
