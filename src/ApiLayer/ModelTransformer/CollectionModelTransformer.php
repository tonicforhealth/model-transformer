<?php

namespace Tonic\Component\ApiLayer\ModelTransformer;

use Tonic\Component\ApiLayer\ModelTransformer\Exception\UnsupportedTransformationException;

/**
 * Handles homogeneous collection of objects.
 */
class CollectionModelTransformer implements ModelTransformerInterface
{
    /**
     * @var ModelTransformerInterface
     */
    private $modelTransformer;

    /**
     * Constructor.
     *
     * @param ModelTransformerInterface $modelTransformer
     */
    public function __construct(ModelTransformerInterface $modelTransformer)
    {
        $this->modelTransformer = $modelTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, $targetClass)
    {
        if (is_array($object) || $object instanceof \Traversable) {
            foreach ($object as $element) {
                if (!$this->modelTransformer->supports($element, $targetClass)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($object, $targetClass)
    {
        if (!$this->supports($object, $targetClass)) {
            throw new UnsupportedTransformationException();
        }

        $elements = [];
        foreach ($object as $element) {
            $elements[] = $this->modelTransformer->transform($element, $targetClass);
        }

        return $elements;
    }
}
