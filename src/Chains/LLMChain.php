<?php

namespace Kambo\Langchain\Chains;

use Kambo\Langchain\LLMs\LLMResult;
use Kambo\Langchain\Prompts\BasePromptTemplate;
use Kambo\Langchain\LLMs\BaseLanguageModel;
use Kambo\Langchain\Prompts\PromptTemplate;
use Kambo\Langchain\ChainedInputs;
use Kambo\Langchain\Exceptions\InvalidFormat;

use function array_intersect_key;
use function array_flip;

/**
 * Chain to run queries against LLMs.
 *
 */
class LLMChain extends Chain
{
    public string $outputKey = 'text';

    /**
     * @param BaseLanguageModel  $llm
     * @param BasePromptTemplate $prompt
     * @param array              $config
     */
    public function __construct(
        public BaseLanguageModel $llm,
        public BasePromptTemplate $prompt,
        array $config = []
    ) {
        $this->outputKey = $config['output_key'] ?? $this->outputKey;
        $memory = $config['memory'] ?? null;
        $callbackManager = $config['callback_manager'] ?? null;
        $verbose = $config['verbose'] ?? null;

        parent::__construct($memory, $callbackManager, $verbose);
    }

    /**
     * Input keys this chain expects.
     *
     * @return array
     */
    public function inputKeys(): array
    {
        return $this->prompt->inputVariables;
    }

    /**
     * Output keys this chain expects.
     *
     * @return array|string[]
     */
    public function outputKeys(): array
    {
        return [$this->outputKey];
    }

    /**
     * Run the logic of this chain and return the output.
     */
    public function call(array $inputs): array
    {
        return $this->apply([$inputs])[0];
    }

    /**
     * Generate LLM result from inputs.
     *
     * @param array $inputList
     *
     * @return LLMResult
     */
    public function generate(array $inputList): LLMResult
    {
        [$prompts, $stop] = $this->prepPrompts($inputList);
        return $this->llm->generatePrompt($prompts, $stop);
    }

    /**
     * Prepare prompts from inputs.
     *
     * @param array $inputList
     *
     * @return array
     */
    public function prepPrompts(array $inputList): array
    {
        $stop = null;
        if (isset($inputList[0]['stop'])) {
            $stop = $inputList[0]['stop'];
        }

        $prompts = [];
        foreach ($inputList as $inputs) {
            $selectedInputs = array_intersect_key($inputs, array_flip($this->prompt->inputVariables));
            $prompt = $this->prompt->format($selectedInputs);
            $text = "Prompt after formatting:\n" . ChainedInputs::getColoredText($prompt, 'green');
            $this->callbackManager->onText($text . "\n", ['verbose' => $this->verbose]);
            if (isset($inputs['stop']) && $inputs['stop'] !== $stop) {
                throw new InvalidFormat('If `stop` is present in any inputs, should be present in all.');
            }

            $prompts[] = $prompt;
        }

        return [$prompts, $stop];
    }

    /**
     * Utilize the LLM generate method for speed gains.
     *
     * @param array $inputList
     *
     * @return array
     */
    public function apply(array $inputList): array
    {
        $response = $this->generate($inputList);
        return $this->createOutputs($response);
    }

    /**
     * Create outputs from response.
     *
     * @param LLMResult $response
     *
     * @return array
     */
    public function createOutputs(LLMResult $response): array
    {
        $outputs = [];
        foreach ($response->generations as $generation) {
            $outputs[] = [$this->outputKey => $generation[0]->text];
        }
        return $outputs;
    }

    /**
     * Format prompt with kwargs and pass to LLM.
     *
     * @param array $parameters Keys to pass to prompt template.
     *
     * @return string Completion from LLM.
     */
    public function predict(array $parameters): string
    {
        return $this($parameters)[$this->outputKey];
    }

    /**
     * Call predict and then parse the results.
     *
     * @param array $kwargs
     *
     * @return string|array
     */
    public function predictAndParse(array $kwargs): string|array
    {
        $result = $this->predict($kwargs);
        if ($this->prompt->outputParser !== null) {
            return $this->prompt->outputParser->parse($result);
        } else {
            return $result;
        }
    }

    /**
     * Call apply and then parse the results.
     *
     * @param array $inputList
     *
     * @return array
     */
    public function applyAndParse(array $inputList): array
    {
        $result = $this->apply($inputList);
        return $this->parseResult($result);
    }

    /** Get the chain type */
    public function getChainType(): string
    {
        return 'llm_chain';
    }

    /**
     * Create LLMChain from LLM and template.
     *
     * @param BaseLanguageModel $llm
     * @param string            $template
     *
     * @return Chain
     */
    public static function fromString(BaseLanguageModel $llm, string $template): Chain
    {
        $promptTemplate = PromptTemplate::fromTemplate($template);

        return new self($llm, $promptTemplate);
    }

    private function parseResult(array $result): array
    {
        $outputs = [];
        foreach ($result as $res) {
            if ($this->prompt->outputParser !== null) {
                $outputs[] = $this->prompt->outputParser->parse($res[$this->outputKey]);
            } else {
                $outputs[] = $res;
            }
        }
        return $outputs;
    }

    public function toArray(): array
    {
        return [
            'memory' => $this->memory?->toArray(),
            'verbose' => $this->verbose,
            'llm' => $this->llm->toArray(),
            'prompt' => $this->prompt->toArray(),
            'output_key' => $this->outputKey,
            '_type' => $this->getChainType(),
        ];
    }
}
