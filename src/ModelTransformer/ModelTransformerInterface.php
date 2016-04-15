<?php

namespace Tonic\Component\ApiLayer\ModelTransformer;

/**
 * Responsible for object transformation.
 */
interface ModelTransformerInterface
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
    public function supports($object, $targetClass);

    /**
     * Transforms object to supported class.
     *
     * @param object|array|\Traversable $object
     * @param string $targetClass
     * @param ContextInterface $context
     *
     * @return array|object|\object[]
     */
    public function transform($object, $targetClass);
}
