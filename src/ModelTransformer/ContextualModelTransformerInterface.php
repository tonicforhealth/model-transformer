<?php

namespace Tonic\Component\ApiLayer\ModelTransformer;

/**
 * Responsible for object transformation with context.
 */
interface ContextualModelTransformerInterface
{
    /**
     * Does transformer support transformation to target class?
     *
     * @param object|array|\Traversable $object
     * @param string $targetClass
     * @param ContextInterface|null $context
     *
     * @return bool
     */
    public function supports($object, $targetClass, ContextInterface $context = null);

    /**
     * Transforms object to supported class with specified context.
     *
     * @param object|array|\Traversable $object
     * @param string $targetClass
     * @param ContextInterface|null $context
     *
     * @return array|object|object[]
     */
    public function transform($object, $targetClass, ContextInterface $context = null);
}
