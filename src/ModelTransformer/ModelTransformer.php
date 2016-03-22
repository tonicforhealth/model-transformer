<?php

namespace Tonic\Component\ApiLayer\ModelTransformer;

use Tonic\Component\ApiLayer\ModelTransformer\Exception\UnsupportedTransformationException;

/**
 * Manages transformers.
 */
class ModelTransformer implements ModelTransformerInterface
{
    /**
     * @var array
     */
    private $modelTransformers = [];

    /**
     * @var array
     */
    private $sorted = [];

    /**
     * @param ModelTransformerInterface $modelTransformer
     *
     * @param int $priority
     * @return $this|ModelTransformerInterface
     */
    public function addModelTransformer(ModelTransformerInterface $modelTransformer, $priority = 0)
    {
        if (!isset($this->modelTransformers[$priority])) {
            $this->modelTransformers[$priority] = [];
        }

        $this->modelTransformers[$priority][] = $modelTransformer;
        unset($this->sorted);

        return $this;
    }

    /**
     * @return ModelTransformerInterface[]
     */
    public function getModelTransformers()
    {
        if (isset($this->sorted)) {
            return $this->sorted;
        }

        krsort($this->modelTransformers);
        $this->sorted = [];
        foreach ($this->modelTransformers as $modelTransformers) {
            $this->sorted = array_merge($this->sorted, $modelTransformers);
        }

        return $this->sorted;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, $targetClass)
    {
        foreach ($this->getModelTransformers() as $modelTransformer) {
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
        $modelTransformer = $this->findSupportedModelTransformer($object, $targetClass);
        if ($modelTransformer) {
            return $modelTransformer->transform($object, $targetClass);
        }

        $objectType = is_object($object) ? get_class($object) : gettype($object);
        throw new UnsupportedTransformationException(sprintf(
            'Can not transform object of type "%s" to object of type "%s"',
            $objectType,
            $targetClass
        ));
    }

    /**
     * Finds and returns model transformer which supports specified object and target class.
     *
     * @param object|array $object
     * @param string $targetClass
     *
     * @return ModelTransformerInterface|null
     */
    public function findSupportedModelTransformer($object, $targetClass)
    {
        foreach ($this->getModelTransformers() as $modelTransformer) {
            if ($modelTransformer->supports($object, $targetClass)) {
                return $modelTransformer;
            }
        }

        return null;
    }
}
