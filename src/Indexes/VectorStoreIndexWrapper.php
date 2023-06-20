<?php

namespace Kambo\Langchain\Indexes;

use Kambo\Langchain\LLMs\BaseLLM;
use Kambo\Langchain\Prompts\BasePromptTemplate;
use Kambo\Langchain\Prompts\PromptTemplate;
use Kambo\Langchain\VectorStores\VectorStore;
use Kambo\Langchain\LLMs\OpenAI;
use Kambo\Langchain\Chains\VectorDbQa\VectorDBQA;

use function array_merge;

/**
 * Wrapper around a vectorstore for easy access.
 */
class VectorStoreIndexWrapper
{
    public function __construct(public VectorStore $vectorStore)
    {
    }

    /**
     * Query the vectorstore.
     *
     * @param string       $question
     * @param BaseLLM|null $llm
     * @param array        $additionalParams
     *
     * @return string
     */
    public function query(
        string $question,
        ?BaseLLM $llm = null,
        ?BasePromptTemplate $promptTemplate = null,
        ?string $documentVariableName = 'context',
        array $additionalParams = []
    ): string {
        $llm = $llm ?? new OpenAI(['temperature' => 0]);
        $chain = VectorDBQA::fromChainType(
            $llm,
            'stuff',
            $promptTemplate,
            $documentVariableName,
            null,
            array_merge(['vectorstore' => $this->vectorStore], $additionalParams)
        );
        return $chain->run($question);
    }

    /**
     * Query the vectorstore and get back sources.
     *
     * @param string       $question
     * @param BaseLLM|null $llm
     * @param array        $additionalParams
     *
     * @return array
     */
    public function queryWithSources(string $question, ?BaseLLM $llm = null, array $additionalParams = []): array
    {
        $llm = $llm ?? new OpenAI(['temperature' => 0]);
        $chain = VectorDBQAWithSourcesChain::from_chain_type(
            $llm,
            array_merge(['vectorstore' => $this->vectorStore], $additionalParams)
        );

        return $chain->run([VectorDBQAWithSourcesChain::QUESTION_KEY => $question]);
    }
}
