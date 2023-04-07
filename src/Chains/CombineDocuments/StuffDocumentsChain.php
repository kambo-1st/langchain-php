<?php

namespace Kambo\Langchain\Chains\CombineDocuments;

use Kambo\Langchain\Prompts\PromptTemplate;
use Kambo\Langchain\Chains\LLMChain;
use Kambo\Langchain\Exceptions\InvalidArgumentException;

use function count;
use function in_array;
use function implode;
use function array_merge;
use function array_intersect_key;
use function array_flip;
use function array_map;

/**
 * Chain that combines documents by stuffing into context.
 */
class StuffDocumentsChain extends BaseCombineDocumentsChain
{
    private LLMChain $llmChain;
    private ?PromptTemplate $documentPrompt;
    private ?string $documentVariableName;

    /**
     * @param LLMChain        $llmChain
     * @param ?PromptTemplate $documentPrompt
     * @param ?string         $documentVariable_name
     */
    public function __construct(
        LLMChain $llmChain,
        ?PromptTemplate $documentPrompt = null,
        ?string $documentVariable_name = null,
    ) {
        if ($documentPrompt === null) {
            $documentPrompt = $this->getDefaultDocumentPrompt();
        }

        $this->llmChain = $llmChain;
        $this->documentPrompt = $documentPrompt;

        if ($documentVariable_name === null) {
            $llmChain_variables = $llmChain->prompt->inputVariables;
            if (count($llmChain_variables) == 1) {
                $documentVariable_name = $llmChain_variables[0];
            } else {
                throw new InvalidArgumentException(
                    'document_variable_name must be provided if there are multiple llm_chain_variables'
                );
            }
        } else {
            $llmChain_variables = $llmChain->prompt->inputVariables;
            if (!in_array($documentVariable_name, $llmChain_variables)) {
                throw new InvalidArgumentException(
                    'document_variable_name '
                    . $documentVariable_name
                    . ' was not found in llm_chain input_variables: '
                    . implode(', ', $llmChain_variables)
                );
            }
        }

        $this->documentVariableName = $documentVariable_name;
    }

    private function getDefaultDocumentPrompt(): PromptTemplate
    {
        return new PromptTemplate('{page_content}', ['page_content']);
    }

    /**
     * @param array $docs
     * @param array $additionalArguments
     *
     * @return array
     */
    private function getInputs(array $docs, array $additionalArguments = []): array
    {
        $docDicts = [];
        foreach ($docs as $doc) {
            $baseInfo = array_merge(['page_content' => $doc->pageContent], $doc->metadata);
            $documentInfo = array_intersect_key($baseInfo, array_flip($this->documentPrompt->inputVariables));
            $docDicts[] = $documentInfo;
        }

        $docStrings = array_map(function ($doc) {
            return $this->documentPrompt->format($doc);
        }, $docDicts);

        $inputs = array_intersect_key($additionalArguments, array_flip($this->llmChain->prompt->inputVariables));
        $inputs[$this->documentVariableName] = implode("\n\n", $docStrings);
        return $inputs;
    }

    /**
     * Get the prompt length by formatting the prompt.
     *
     * @param array $docs
     * @param array $kwargs
     *
     * @return int|null
     */
    public function promptLength(array $docs, array $kwargs = []): ?int
    {
        $inputs = $this->getInputs($docs, $kwargs);
        $prompt = $this->llmChain->prompt->format($inputs);
        return $this->llmChain->llm->getNumTokens($prompt);
    }

    /**
     * Stuff all documents into one prompt and pass to LLM.
     *
     * @param array $docs
     * @param array $kwargs
     *
     * @return array
     */
    public function combineDocs(array $docs, array $kwargs = []): array
    {
        $inputs = $this->getInputs($docs, $kwargs);

        return [$this->llmChain->predict($inputs), []];
    }

    public function getChainType(): string
    {
        return 'stuff_documents_chain';
    }

    public function toArray(): array
    {
        return [
            'memory' => $this->memory?->toArray(),
            'verbose' => $this->verbose,
            'input_key' => $this->inputKey,
            'output_key' => $this->outputKey,
            'document_variable_name' => $this->documentVariableName,
            'llm_chain' => $this->llmChain->toArray(),
            'document_prompt' => $this->documentPrompt->toArray(),
            '_type' => $this->getChainType(),
        ];
    }
}
