<?php

namespace spec\Tonic\Component\ApiLayer\ModelTransformer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Tonic\Component\ApiLayer\ModelTransformer\Exception\UnsupportedTransformationException;
use Tonic\Component\ApiLayer\ModelTransformer\ModelTransformer;
use Tonic\Component\ApiLayer\ModelTransformer\ModelTransformerInterface;

/**
 * @codingStandardsIgnoreStart
 */
class CollectionModelTransformerSpec extends ObjectBehavior
{
    function let(ModelTransformer $modelTransformer)
    {
        $this->beAnInstanceOf('Tonic\Component\ApiLayer\ModelTransformer\CollectionModelTransformer');
        $this->beConstructedWith($modelTransformer);
        $this->shouldImplement(ModelTransformerInterface::class);
    }

    function it_should_support_transformation_if_every_object_can_be_transformed(
        ModelTransformer $modelTransformer
    )
    {
        $modelTransformer
            ->supports(new \DateTime(), \stdClass::class)
            ->willReturn(true)
        ;

        $modelTransformer
            ->supports(new \stdClass(), \stdClass::class)
            ->willReturn(true)
        ;

        $this
            ->supports([new \DateTime(), new \stdClass()], \stdClass::class)
            ->shouldBe(true)
        ;
    }

    function it_should_not_support_transformation_if_at_least_one_object_can_not_be_transformed(
        ModelTransformer $modelTransformer
    )
    {
        $modelTransformer
            ->supports(new \DateTime(), \stdClass::class)
            ->willReturn(true)
        ;

        $modelTransformer
            ->supports(new \stdClass(), \stdClass::class)
            ->willReturn(false)
        ;

        $this
            ->supports([new \DateTime(), new \stdClass()], \stdClass::class)
            ->shouldBe(false)
        ;
    }

    function it_should_transform_collection(ModelTransformer $modelTransformer)
    {
        $modelTransformer
            ->findSupportedModelTransformer(new \DateTime(), \stdClass::class)
            ->willReturn($modelTransformer)
        ;

        $modelTransformer
            ->supports(new \DateTime(), \stdClass::class)
            ->willReturn(true)
        ;

        $modelTransformer
            ->transform(new \DateTime(), \stdClass::class)
            ->willReturn(new \stdClass())
        ;

        $this
            ->transform([new \DateTime()], \stdClass::class)
            ->shouldBeLike([new \stdClass()])
        ;
    }

    function it_can_transform_empty_arrays()
    {
        $this->supports([], 'SomeClass')->shouldBe(true);

        $this->transform([], 'SomeClass')->shouldBeLike([]);
    }

    function it_should_throw_exception_if_it_can_not_transform_at_least_one_collection_element(
        ModelTransformer $modelTransformer
    )
    {
        $modelTransformer
            ->findSupportedModelTransformer(new \DateTime(), \stdClass::class)
            ->willReturn($modelTransformer)
        ;

        $modelTransformer
            ->supports(new \DateTime(), \stdClass::class)
            ->willReturn(false)
        ;

        $this
            ->shouldThrow(UnsupportedTransformationException::class)
            ->duringTransform([new \DateTime()], \stdClass::class)
        ;
    }
}
