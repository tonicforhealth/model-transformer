<?php

namespace spec\Tonic\Component\ApiLayer\ModelTransformer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Tonic\Component\ApiLayer\ModelTransformer\ContextInterface;
use Tonic\Component\ApiLayer\ModelTransformer\ContextualModelTransformerInterface;
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

    function it_should_add_contextual_transformer(ContextualModelTransformerInterface $contextualModelTransformer)
    {
        $this->getModelTransformers()->shouldHaveCount(0);

        $this->addModelTransformer($contextualModelTransformer)->shouldBe($this->getWrappedObject());

        $this->getModelTransformers()->shouldHaveCount(1);
    }

    function it_should_not_add_anything_else_except_model_transformers()
    {
        $this->shouldThrow(\RuntimeException::class)->duringAddModelTransformer(new \stdClass());
    }

    function it_should_pass_context_to_contextual_model_transformers(
        ContextualModelTransformerInterface $contextualModelTransformer,
        ContextInterface $context
    )
    {
        $this->addModelTransformer($contextualModelTransformer);

        $contextualModelTransformer->supports(new \stdClass(), 'SomeClass', $context)->shouldBeCalled()->willReturn(true);

        $this->supports(new \stdClass(), 'SomeClass', $context)->shouldBe(true);
    }

    function it_should_not_pass_context_to_model_transformers(
        ModelTransformerInterface $notContextualModelTransformer,
        ContextInterface $context
    )
    {
        $this->addModelTransformer($notContextualModelTransformer);

        $notContextualModelTransformer->supports(new \stdClass(), 'SomeClass')->shouldBeCalled()->willReturn(true);

        $this->supports(new \stdClass(), 'SomeClass', $context)->shouldBe(true);
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
            ->shouldBeCalled();

        $secondTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(true)
            ->shouldBeCalled();

        $thirdTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(false)
            ->shouldNotBeCalled();

        $this->supports(new \stdClass(), 'SomeClass')->shouldBe(true);
    }

    function it_should_support_target_class_if_at_least_one_transformer_supports_it(
        ModelTransformerInterface $supportedModelTransformer,
        ModelTransformerInterface $notSupportedModelTransformer
    )
    {
        $supportedModelTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(true);

        $notSupportedModelTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(false);

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
            ->willReturn(false);

        $notSupportedModelTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(false);

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
            ->willReturn(false);

        $notSupportedModelTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(false);

        $this->addModelTransformer($supportedModelTransformer);
        $this->addModelTransformer($notSupportedModelTransformer);

        $this
            ->shouldThrow(UnsupportedTransformationException::class)
            ->duringTransform(new \stdClass(), 'SomeClass');
    }

    function it_should_pass_context_during_transformation_to_contextual_model_transformer(
        ContextualModelTransformerInterface $contextualModelTransformer,
        ContextInterface $context
    )
    {
        $this->addModelTransformer($contextualModelTransformer);

        $contextualModelTransformer
            ->supports(new \stdClass(), 'SomeClass', $context)
            ->shouldBeCalled()
            ->willReturn(true);

        $contextualModelTransformer
            ->transform(new \stdClass(), 'SomeClass', $context)
            ->shouldBeCalled()
            ->willReturn((object)['a' => 1]);

        $this
            ->transform(new \stdClass(), 'SomeClass', $context)
            ->shouldBeLike((object)['a' => 1]);
    }

    function it_should_not_pass_context_during_transformation_to_model_transformer(
        ModelTransformerInterface $modelTransformer,
        ContextInterface $context
    )
    {
        $this->addModelTransformer($modelTransformer);

        $modelTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->shouldBeCalled()
            ->willReturn(true);

        $modelTransformer
            ->transform(new \stdClass(), 'SomeClass')
            ->shouldBeCalled()
            ->willReturn((object)['a' => 1]);

        $this
            ->transform(new \stdClass(), 'SomeClass', $context)
            ->shouldBeLike((object)['a' => 1]);
    }


    function it_should_transform_object_if_at_least_one_transformer_transforms_it(
        ModelTransformerInterface $notSupportedModelTransformer,
        ModelTransformerInterface $supportedModelTransformer
    )
    {
        $notSupportedModelTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(false);

        $supportedModelTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(true);

        $supportedModelTransformer
            ->transform(new \stdClass(), 'SomeClass')
            ->willReturn((object)['a' => 1]);

        $this->addModelTransformer($notSupportedModelTransformer);
        $this->addModelTransformer($supportedModelTransformer);

        $this
            ->transform(new \stdClass(), 'SomeClass')
            ->shouldBeLike((object)['a' => 1]);
    }

    public function it_should_pass_context_to_contextual_model_transformer(
        ContextualModelTransformerInterface $contextualModelTransformer,
        ContextInterface $context
    )
    {
        $contextualModelTransformer
            ->supports(new \stdClass(), 'SomeClass', $context)
            ->shouldBeCalled()
            ->willReturn(true);

        $this->addModelTransformer($contextualModelTransformer);

        $this
            ->findSupportedModelTransformer(new \stdClass(), 'SomeClass', $context)
            ->shouldBe($contextualModelTransformer);
    }

    public function it_should_not_pass_context_to_model_transformer(
        ModelTransformerInterface $modelTransformer,
        ContextInterface $context
    )
    {
        $modelTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->shouldBeCalled()
            ->willReturn(true);

        $this->addModelTransformer($modelTransformer);

        $this
            ->findSupportedModelTransformer(new \stdClass(), 'SomeClass', $context)
            ->shouldBe($modelTransformer);
    }

    public function it_should_find_and_return_supported_model_transformer(
        ModelTransformerInterface $notSupportedModelTransformer,
        ModelTransformerInterface $supportedModelTransformer
    )
    {
        $notSupportedModelTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(false);

        $supportedModelTransformer
            ->supports(new \stdClass(), 'SomeClass')
            ->willReturn(true);

        $this->addModelTransformer($notSupportedModelTransformer);
        $this->addModelTransformer($supportedModelTransformer);

        $this
            ->findSupportedModelTransformer(new \stdClass(), 'SomeClass')
            ->shouldBe($supportedModelTransformer);
    }
}
