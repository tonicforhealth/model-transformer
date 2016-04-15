<?php

namespace Tonic\Component\ApiLayer\ModelTransformer;

/**
 * Represents model transformation context.
 */
class Context implements ContextInterface
{
    /**
     * @var mixed|null
     */
    private $payload;

    /**
     * Constructor.
     *
     * @param mixed|null $payload
     */
    public function __construct($payload = null)
    {
        $this->payload = $payload;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload()
    {
        return $this->payload;
    }
}