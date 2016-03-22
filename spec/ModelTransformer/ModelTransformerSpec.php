<?php

namespace spec\Tonic\Component\ApiLayer\ModelTransformer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Tonic\Component\ApiLayer\ModelTransformer\ModelTransformerInterface;
use Tonic\Component\ApiLayer\ModelTransformer\Exception\UnsupportedTransformationException;

/**
 * @codingStandardsIgnoreStart
 */
class ModelTransformerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beAnInstanceOf('Tonic\Component\ApiLayer\ModelTransformer\ModelTransformer');
        $this->shouldImplement(ModelTransformerInterface::class);
    }

    function it_should_add_transformer(ModelTransformerInterface $modelTransformer)
    {
        $this->getModelTransformers()->shouldHaveCount(0);

        $this->addModelTransformer($modelTransformer)->shouldBe($this->getWrappedObject());

        $this->getModelTransformers()->shouldHaveCount(1);
    }

    function it_should_prioritize_transformers(
        ModelTransformerInterface $firstTransformer,
        ModelTransformerInterface $secondTransformer,
        ModelTransformerInterface $thirdTransformer
    )
    {
        $this->addModelTransformer($secondTransformer, 2)->shouldBe($this->getWrappedObject());
        $this->addModelTransformer($thirdTransformer, 2)->shouldBe($this->getWrappedObject());
        $this->addModelTransformer($firstTransformer, 15)->shouldBe($this->getWrappedObject());

        $firstTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(false)
            ->shouldBeCalled()
        ;

        $secondTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(true)
            ->shouldBeCalled()
        ;

        $thirdTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(false)
            ->shouldNotBeCalled()
        ;

        $this->supports(new \stdClass(), 'SomeClass')->shouldBe(true);
    }

    function it_should_support_target_class_if_at_least_one_transformer_supports_it(
        ModelTransformerInterface $supportedModelTransformer,
        ModelTransformerInterface $notSupportedModelTransformer
    )
    {
        $supportedModelTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(true)
        ;

        $notSupportedModelTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(false)
        ;

        $this->addModelTransformer($supportedModelTransformer);
        $this->addModelTransformer($notSupportedModelTransformer);

        $this->supports(new \stdClass(), 'SomeClass')->shouldBe(true);
    }

    function it_should_not_support_target_class_if_transformers_do_not_support_it(
        ModelTransformerInterface $supportedModelTransformer,
        ModelTransformerInterface $notSupportedModelTransformer
    )
    {
        $supportedModelTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(false)
        ;

        $notSupportedModelTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(false)
        ;

        $this->addModelTransformer($supportedModelTransformer);
        $this->addModelTransformer($notSupportedModelTransformer);

        $this->supports(new \stdClass(), 'SomeClass')->shouldBe(false);
    }

    function it_should_throw_exception_if_transformers_can_not_transform_object_to_target_class(
        ModelTransformerInterface $supportedModelTransformer,
        ModelTransformerInterface $notSupportedModelTransformer
    )
    {
        $supportedModelTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(false)
        ;

        $notSupportedModelTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(false)
        ;

        $this->addModelTransformer($supportedModelTransformer);
        $this->addModelTransformer($notSupportedModelTransformer);

        $this
            ->shouldThrow(UnsupportedTransformationException::class)
            ->duringTransform(new \stdClass(), 'SomeClass')
        ;
    }

    function it_should_transform_object_if_at_least_one_transformer_transforms_it(
        ModelTransformerInterface $notSupportedModelTransformer,
        ModelTransformerInterface $supportedModelTransformer
    )
    {
        $notSupportedModelTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(false)
        ;

        $supportedModelTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(true)
        ;

        $supportedModelTransformer
            ->transform(new \stdClass(), 'SomeClass')
            ->willReturn((object) ['a' => 1])
        ;

        $this->addModelTransformer($notSupportedModelTransformer);
        $this->addModelTransformer($supportedModelTransformer);

        $this
            ->transform(new \stdClass(), 'SomeClass')
            ->shouldBeLike((object) ['a' => 1])
        ;
    }
}
