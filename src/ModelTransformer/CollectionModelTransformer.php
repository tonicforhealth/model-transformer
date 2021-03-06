<?php

namespace Tonic\Component\ApiLayer\ModelTransformer;

use Tonic\Component\ApiLayer\ModelTransformer\Exception\UnsupportedTransformationException;

/**
 * Handles collection of objects.
 */
class CollectionModelTransformer implements ModelTransformerInterface
{
    /**
     * @var ModelTransformer
     */
    private $modelTransformer;

    /**
     * Constructor.
     *
     * @param ModelTransformer $modelTransformer
     */
    public function __construct(ModelTransformer $modelTransformer)
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
        if (count($object) == 0) {
            return [];
        }

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
