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
     * @param int $priority
     *
     * @return $this|ModelTransformerInterface
     *
     * @throws \RuntimeException
     */
    public function addModelTransformer($modelTransformer, $priority = 0)
    {
        if (!(($modelTransformer instanceof ModelTransformerInterface) || ($modelTransformer instanceof ContextualModelTransformerInterface))) {
            throw new \RuntimeException(
                sprintf('Model transformer should be an instance of "%s" or "%s"', ModelTransformerInterface::class, ContextualModelTransformerInterface::class)
            );
        }

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
        /** @var ContextInterface $context */
        $context = (func_num_args() == 3) ? func_get_arg(2) : null;

        foreach ($this->getModelTransformers() as $modelTransformer) {
            if (($modelTransformer instanceof ContextualModelTransformerInterface) && $modelTransformer->supports($object, $targetClass, $context)) {
                return true;
            }

            if (($modelTransformer instanceof ModelTransformerInterface) && $modelTransformer->supports($object, $targetClass)) {
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
        /** @var ContextInterface $context */
        $context = (func_num_args() == 3) ? func_get_arg(2) : null;

        $modelTransformer = $this->findSupportedModelTransformer($object, $targetClass, $context);
        if ($modelTransformer instanceof ContextualModelTransformerInterface) {
            return $modelTransformer->transform($object, $targetClass, $context);
        }

        if (($modelTransformer instanceof ModelTransformerInterface) && $modelTransformer->supports($object, $targetClass)) {
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
     * @param ContextInterface|null $context
     *
     * @return null|ModelTransformerInterface
     */
    public function findSupportedModelTransformer($object, $targetClass, ContextInterface $context = null)
    {
        foreach ($this->getModelTransformers() as $modelTransformer) {
            if (($modelTransformer instanceof ContextualModelTransformerInterface) && $modelTransformer->supports($object, $targetClass, $context)) {
                return $modelTransformer;
            }

            if (($modelTransformer instanceof ModelTransformerInterface) && $modelTransformer->supports($object, $targetClass)) {
                return $modelTransformer;
            }
        }

        return null;
    }
}
