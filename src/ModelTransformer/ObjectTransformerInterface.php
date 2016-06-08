<?php

namespace Tonic\Component\ApiLayer\ModelTransformer;

/**
 * Responsible only for object transformation.
 */
interface ObjectTransformerInterface
{
    /**
     * Returns class name of supported instances for transformation.
     *
     * @return string
     */
    public function getSupportedClass();

    /**
     * Returns supported target class.
     *
     * @return string
     */
    public function getTargetClass();

    /**
     * Transforms object of supported class to target.
     *
     * @param object $object
     *
     * @return object
     */
    public function transform($object);
}