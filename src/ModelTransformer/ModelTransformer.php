<?php

namespace Tonic\Component\ApiLayer\ModelTransformer;

use Tonic\Component\ApiLayer\ModelTransformer\Exception\UnsupportedTransformationException;

/**
 * Manages transformers.
 */
class ModelTransformer implements ModelTransformerInterface
{
    /**
     * @var ModelTransformerInterface[]
     */
    private $modelTransformers = [];

    /**
     * @param ModelTransformerInterface $modelTransformer
     *
     * @return $this|ModelTransformerInterface
     */
    public function addModelTransformer(ModelTransformerInterface $modelTransformer)
    {
        $this->modelTransformers[] = $modelTransformer;

        return $this;
    }

    /**
     * @return ModelTransformerInterface[]
     */
    public function getModelTransformers()
    {
        return $this->modelTransformers;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, $targetClass)
    {
        foreach ($this->modelTransformers as $modelTransformer) {
            if ($modelTransformer->supports($object, $targetClass)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($object, $targetClass)
    {
        foreach ($this->modelTransformers as $modelTransformer) {
            if ($modelTransformer->supports($object, $targetClass)) {
                return $modelTransformer->transform($object, $targetClass);
            }
        }

        $objectType = is_object($object) ? get_class($object) : gettype($object);
        throw new UnsupportedTransformationException(sprintf(
            'Can not transform object of type "%s" to object of type "%s"',
            $objectType,
            $targetClass
        ));
    }
}
