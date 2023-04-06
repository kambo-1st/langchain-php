<?php

namespace Kambo\Langchain\Prompts;

use Kambo\Langchain\Message\BaseMessage;
use Kambo\Langchain\Exceptions\InvalidArgumentException;
use Kambo\Langchain\Exceptions\NotImplemented;
use SplFileInfo;

use function array_map;
use function array_merge;
use function array_values;
use function array_unique;
use function array_intersect_key;
use function array_flip;

class ChatPromptTemplate extends BaseChatPromptTemplate
{
    public function __construct(
        public array $messages,
        public array $inputVariables,
    ) {
    }

    public static function fromRoleStrings(array $stringMessages): ChatPromptTemplate
    {
        $messages = array_map(
            function ($tuple) {
                [$role, $template] = $tuple;
                return new ChatMessagePromptTemplate(
                    PromptTemplate::fromTemplate($template),
                    $role
                );
            },
            $stringMessages
        );

        return self::fromMessages($messages);
    }

    public static function fromStrings(array $stringMessages): ChatPromptTemplate
    {
        $messages = array_map(
            function ($tuple) {
                [$role, $template] = $tuple;
                return new $role([
                    'content' => PromptTemplate::fromTemplate($template)
                ]);
            },
            $stringMessages
        );

        return self::fromMessages($messages);
    }

    public static function fromMessages(array $messages): ChatPromptTemplate
    {
        $inputVars = [];

        foreach ($messages as $message) {
            if ($message instanceof BaseMessagePromptTemplate) {
                $inputVars = array_merge($inputVars, $message->getInputVariables());
            }
        }

        return new self($messages, array_values(array_unique($inputVars)));
    }

    public function format(array $arguments = []): string
    {
        return (string)$this->formatPrompt($arguments);
    }

    public function getInputVariables(): array
    {
        return $this->inputVariables;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    protected function formatMessages(array $arguments = []): array
    {
        $arguments = $this->mergePartialAndUserVariables($arguments);
        $result = [];

        foreach ($this->messages as $messageTemplate) {
            if ($messageTemplate instanceof BaseMessage) {
                $result[] = $messageTemplate;
            } elseif ($messageTemplate instanceof BaseMessagePromptTemplate) {
                $relParams = array_intersect_key($arguments, array_flip($messageTemplate->getInputVariables()));
                $message = $messageTemplate->formatMessages($relParams);
                $result = array_merge($result, $message);
            } else {
                throw new InvalidArgumentException('Unexpected input: ' . $messageTemplate);
            }
        }

        return $result;
    }

    public function partial(array $arguments = []): BasePromptTemplate
    {
        throw new NotImplemented('Method not implemented.');
    }

    public function getPromptType(): string
    {
        throw new NotImplemented('Method not implemented.');
    }

    public function save(string|SplFileInfo $filePath): void
    {
        throw new NotImplemented('Method not implemented.');
    }

    public function toArray()
    {
        return [
            'messages' => $this->messages,
            'inputVariables' => $this->inputVariables,
        ];
    }
}
