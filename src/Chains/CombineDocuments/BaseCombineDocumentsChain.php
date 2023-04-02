<?php

namespace Kambo\Langchain\Chains\CombineDocuments;

use Kambo\Langchain\Chains\Chain;

use function array_filter;

use const ARRAY_FILTER_USE_KEY;

abstract class BaseCombineDocumentsChain extends Chain
{
    private $inputKey = 'input_documents';
    private $outputKey = 'output_text';

    public function inputKeys(): array
    {
        return [$this->input_key];
    }

    public function outputKeys(): array
    {
        return [$this->output_key];
    }

    public function promptLength(array $docs, array $kwargs = []): ?int
    {
        return null;
    }

    abstract public function combineDocs(array $docs, array $kwargs = []): array;

    protected function call(array $inputs): array
    {
        $docs = $inputs[$this->input_key];
        $otherKeys = array_filter($inputs, function ($key) {
            return $key != $this->input_key;
        }, ARRAY_FILTER_USE_KEY);

        $outputAnd_extra_return = $this->combineDocs($docs, $otherKeys);
        $output = $outputAnd_extra_return[0];
        $extraReturn_dict = $outputAnd_extra_return[1];
        $extraReturn_dict[$this->output_key] = $output;
        return $extraReturn_dict;
    }
}
