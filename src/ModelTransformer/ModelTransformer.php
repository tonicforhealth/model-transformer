<?php

namespace Tonic\Component\ApiLayer\ModelTransformer;

use Tonic\Component\ApiLayer\ModelTransformer\Exception\UnsupportedTransformationException;

/**
 * Manages transformers.
 */
class ModelTransformer implements ModelTransformerInterface
{
    /**
     * @var ModelTransformerInterface[]|ContextualModelTransformerInterface[]
     */
    private $modelTransformers = [];

    /**
     * @var ObjectTransformerInterface[]
     */
    private $objectTransformers = [];

    /**
     * @var array
     */
    private $sortedModelTransformers = [];

    /**
     * @var array
     */
    private $sortedObjectTransformers = [];

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
        if (!(($modelTransformer instanceof ModelTransformerInterface)
            || ($modelTransformer instanceof ContextualModelTransformerInterface)
            || ($modelTransformer instanceof ObjectTransformerInterface)
        )
        ) {
            throw new \RuntimeException(
                sprintf('Model transformer should be an instance of "%s", "%s" or "%s"', ModelTransformerInterface::class, ContextualModelTransformerInterface::class, ObjectTransformerInterface::class)
            );
        }

        if ($modelTransformer instanceof ObjectTransformerInterface) {
            if (!isset($this->objectTransformers[$modelTransformer->getSupportedClass()])) {
                $this->objectTransformers[$modelTransformer->getSupportedClass()] = [];
            }

            if (!isset($this->objectTransformers[$modelTransformer->getSupportedClass()][$modelTransformer->getTargetClass()])) {
                $this->objectTransformers[$modelTransformer->getSupportedClass()][$modelTransformer->getTargetClass()] = [];
            }

            $this->objectTransformers[$modelTransformer->getSupportedClass()][$modelTransformer->getTargetClass()][$priority] = $modelTransformer;
            unset($this->sortedObjectTransformers);

            return $this;
        }

        if (!isset($this->modelTransformers[$priority])) {
            $this->modelTransformers[$priority] = [];
        }

        $this->modelTransformers[$priority][] = $modelTransformer;
        unset($this->sortedModelTransformers);

        return $this;
    }

    /**
     * @return ObjectTransformerInterface[]
     */
    public function getObjectTransformers()
    {
        if (isset($this->sortedObjectTransformers)) {
            return $this->sortedObjectTransformers;
        }

        $this->sortedObjectTransformers = [];
        foreach ($this->objectTransformers as $supportedClass => $objectTransformersBySupportedClass) {
            $this->sortedObjectTransformers[$supportedClass] = [];
            foreach ($objectTransformersBySupportedClass as $targetClass => $objectTransformersByPriorities) {
                ksort($objectTransformersByPriorities);
                $this->sortedObjectTransformers[$supportedClass][$targetClass] = array_values($objectTransformersByPriorities)[0];
            }
        }

        return $this->sortedObjectTransformers;
    }

    /**
     * @return ModelTransformerInterface[]
     */
    public function getModelTransformers()
    {
        if (isset($this->sortedModelTransformers)) {
            return $this->sortedModelTransformers;
        }

        krsort($this->modelTransformers);
        $this->sortedModelTransformers = [];
        foreach ($this->modelTransformers as $modelTransformers) {
            $this->sortedModelTransformers = array_merge($this->sortedModelTransformers, $modelTransformers);
        }

        return $this->sortedModelTransformers;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, $targetClass)
    {
        /** @var ContextInterface $context */
        $context = (func_num_args() == 3) ? func_get_arg(2) : null;

        $objectTransformers = $this->getObjectTransformers();
        if (isset($objectTransformers[get_class($object)]) && isset($objectTransformers[get_class($object)][$targetClass])) {
            return true;
        }

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

        if ($modelTransformer instanceof ObjectTransformerInterface) {
            return $modelTransformer->transform($object);
        }

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
     * @return null|ModelTransformerInterface|ContextualModelTransformerInterface|ObjectTransformerInterface
     */
    public function findSupportedModelTransformer($object, $targetClass, ContextInterface $context = null)
    {
        $objectTransformers = $this->getObjectTransformers();
        if (isset($objectTransformers[get_class($object)]) && isset($objectTransformers[get_class($object)][$targetClass])) {
            return $objectTransformers[get_class($object)][$targetClass];
        }

        foreach ($this->getModelTransformers() as $modelTransformer) {
            if (($modelTransformer instanceof ContextualModelTransformerInterface) && $modelTransformer->supports($object, $targetClass, $context)) {
                return $modelTransformer;
            }

            if (($modelTransformer instanceof ModelTransformerInterface) && $modelTransformer->supports($object, $targetClass)) {
                return $modelTransformer;
            }

            if ($modelTransformer instanceof ObjectTransformerInterface) {
                continue;
            }
        }

        return null;
    }
}
