<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Factories;

use TinyBlocks\Mapper\Internal\Detectors\DateTimeDetector;
use TinyBlocks\Mapper\Internal\Detectors\EnumDetector;
use TinyBlocks\Mapper\Internal\Detectors\ValueObjectDetector;
use TinyBlocks\Mapper\Internal\Extractors\IterableExtractor;
use TinyBlocks\Mapper\Internal\Extractors\ReflectionExtractor;
use TinyBlocks\Mapper\Internal\Extractors\ValuePropertyExtractor;
use TinyBlocks\Mapper\Internal\Resolvers\RecursiveValueResolver;
use TinyBlocks\Mapper\Internal\Resolvers\StrategyResolver;
use TinyBlocks\Mapper\Internal\Resolvers\StrategyResolverContainer;
use TinyBlocks\Mapper\Internal\Strategies\ComplexObjectMappingStrategy;
use TinyBlocks\Mapper\Internal\Strategies\DateTimeMappingStrategy;
use TinyBlocks\Mapper\Internal\Strategies\EnumMappingStrategy;
use TinyBlocks\Mapper\Internal\Strategies\IterableMappingStrategy;
use TinyBlocks\Mapper\Internal\Transformers\DateTimeTransformer;
use TinyBlocks\Mapper\Internal\Transformers\EnumTransformer;
use TinyBlocks\Mapper\Internal\Transformers\ValueObjectUnwrapper;

final readonly class StrategyResolverFactory
{
    public function create(): StrategyResolver
    {
        $reflectionExtractor = new ReflectionExtractor();
        $valueObjectDetector = new ValueObjectDetector();

        $valueObjectUnwrapper = new ValueObjectUnwrapper(
            extractor: new ValuePropertyExtractor(),
            valueObjectDetector: $valueObjectDetector
        );

        $resolverContainer = new StrategyResolverContainer();

        $recursiveValueResolver = new RecursiveValueResolver(
            unwrapper: $valueObjectUnwrapper,
            resolverContainer: $resolverContainer,
            valueObjectDetector: $valueObjectDetector
        );

        $default = new ComplexObjectMappingStrategy(
            extractor: $reflectionExtractor,
            resolver: $recursiveValueResolver
        );

        $resolver = new StrategyResolver(
            $default,
            new EnumMappingStrategy(
                detector: new EnumDetector(),
                transformer: new EnumTransformer()
            ),
            new DateTimeMappingStrategy(
                detector: new DateTimeDetector(),
                transformer: new DateTimeTransformer()
            ),
            new IterableMappingStrategy(
                extractor: new IterableExtractor(extractor: $reflectionExtractor),
                resolver: $recursiveValueResolver
            )
        );

        $resolverContainer->set(resolver: $resolver);

        return $resolver;
    }
}
