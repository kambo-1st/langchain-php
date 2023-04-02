<?php

namespace Kambo\Langchain\Callbacks;

/**
 * Base callback manager that can be used to handle callbacks from LangChain.
 */
abstract class BaseCallbackManager extends BaseCallbackHandler
{
    /**
     * Add a handler to the callback manager.
     *
     * @param BaseCallbackHandler $callback
     *
     * @return void
     */
    abstract public function addHandler(BaseCallbackHandler $callback): void;

    /**
     * Remove a handler from the callback manager.
     *
     * @param BaseCallbackHandler $handler
     *
     * @return void
     */
    abstract public function removeHandler(BaseCallbackHandler $handler): void;

    /**
     * Set handler as the only handler on the callback manager.
     *
     * @param BaseCallbackHandler $handler
     *
     * @return void
     */
    public function setHandler(BaseCallbackHandler $handler): void
    {
        /** Set handler as the only handler on the callback manager. */
        $this->setHandlers([$handler]);
    }

    /**
     * Set handlers as the only handlers on the callback manager.
     *
     * @param array $handlers
     *
     * @return void
     */
    abstract public function setHandlers(array $handlers): void;
}
