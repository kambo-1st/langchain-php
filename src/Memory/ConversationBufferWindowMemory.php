<?php

namespace Kambo\Langchain\Memory;

use Kambo\Langchain\Message\HumanMessage;
use Kambo\Langchain\Message\AIMessage;
use Kambo\Langchain\Message\SystemMessage;
use Kambo\Langchain\Message\ChatMessage;
use Kambo\Langchain\Message\Utils;
use Exception;

use function array_slice;
use function sprintf;
use function get_class;
use function implode;

/**
 * Buffer for storing conversation memory.
 */
class ConversationBufferWindowMemory extends BaseChatMemory
{
    private $humanPrefix = 'Human';
    private $aiPrefix = 'AI';
    private $memoryKey = 'history';
    private $k = 5;

    public function __construct(array $config = [])
    {
        $this->k = $config['k'] ?? $this->k;
        $this->humanPrefix = $config['human_prefix'] ?? $this->humanPrefix;
        $this->aiPrefix = $config['ai_prefix'] ?? $this->aiPrefix;
        $this->memoryKey = $config['memory_key'] ?? $this->memoryKey;

        parent::__construct();
    }

    public function getBuffer()
    {
        return $this->chatMemory->toArray();
    }

    public function getMemoryVariables()
    {
        return [$this->memoryKey];
    }

    /**
     * Return history buffer.
     *
     * @param array $inputs
     *
     * @return array
     */
    public function loadMemoryVariables(array $inputs = []): array
    {
        if ($this->returnMessages) {
            $buffer = $this->getBuffer();
            $buffer = array_slice($buffer, -$this->k * 2, $this->k * 2);
        } else {
            $buffer = $this->getBufferString(
                array_slice($this->getBuffer(), -$this->k * 2, $this->k * 2),
                $this->humanPrefix,
                $this->aiPrefix,
            );
        }

        return [$this->memoryKey => $buffer];
    }


    /**
     * Get buffer string of messages.
     *
     * @param array  $messages
     * @param string $humanPrefix
     * @param string $aiPrefix
     *
     * @return string
     */
    public function getBufferString(array $messages, string $humanPrefix = 'Human', string $aiPrefix = 'AI'): string
    {
        return Utils::getBufferString($messages, $humanPrefix, $aiPrefix);
    }
}
