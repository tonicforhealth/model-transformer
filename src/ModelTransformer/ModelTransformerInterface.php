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
     *
     * @return bool
     */
    public function supports($object, $targetClass);

    /**
     * Transforms object to supported class.
     *
     * @param object|array|\Traversable $object
     * @param string $targetClass
     *
     * @return array|object|object[]
     */
    public function transform($object, $targetClass);
}
