<?php

namespace Kambo\Langchain\Message;

use Kambo\Langchain\Exceptions\IllegalState;

use function sprintf;
use function get_class;
use function implode;

class Utils
{
    /**
     * Get buffer string of messages.
     *
     * @param array  $messages
     * @param string $humanPrefix
     * @param string $aiPrefix
     *
     * @return string
     */
    public static function getBufferString(
        array $messages,
        string $humanPrefix = 'Human',
        string $aiPrefix = 'AI'
    ): string {
        $stringMessages = [];
        foreach ($messages as $m) {
            if ($m instanceof HumanMessage) {
                $role = $humanPrefix;
            } elseif ($m instanceof AIMessage) {
                $role = $aiPrefix;
            } elseif ($m instanceof SystemMessage) {
                $role = 'System';
            } elseif ($m instanceof ChatMessage) {
                $role = $m->getRole();
            } else {
                throw new IllegalState(sprintf('Got unsupported message type: %s', get_class($m)));
            }

            $stringMessages[] = sprintf('%s: %s', $role, $m->content);
        }

        return implode("\n", $stringMessages);
    }
}
