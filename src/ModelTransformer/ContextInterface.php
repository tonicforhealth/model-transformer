<?php

namespace Tonic\Component\ApiLayer\ModelTransformer;

/**
 * Represents model transformation context.
 */
interface ContextInterface
{
    /**
     * Domain specific data attached to context.
     *
     * @return mixed|null
     */
    public function getPayload();
}