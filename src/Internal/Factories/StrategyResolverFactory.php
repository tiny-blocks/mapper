<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Factories;

use TinyBlocks\Mapper\Internal\Detectors\CollectibleDetector;
use TinyBlocks\Mapper\Internal\Detectors\DateTimeDetector;
use TinyBlocks\Mapper\Internal\Detectors\EnumDetector;
use TinyBlocks\Mapper\Internal\Detectors\ScalarDetector;
use TinyBlocks\Mapper\Internal\Detectors\ValueObjectDetector;
use TinyBlocks\Mapper\Internal\Extractors\ReflectionExtractor;
use TinyBlocks\Mapper\Internal\Extractors\ValuePropertyExtractor;
use TinyBlocks\Mapper\Internal\Resolvers\StrategyResolver;
use TinyBlocks\Mapper\Internal\Resolvers\StrategyResolverContainer;
use TinyBlocks\Mapper\Internal\Strategies\CollectionMappingStrategy;
use TinyBlocks\Mapper\Internal\Strategies\ComplexObjectMappingStrategy;
use TinyBlocks\Mapper\Internal\Strategies\DateTimeMappingStrategy;
use TinyBlocks\Mapper\Internal\Strategies\EnumMappingStrategy;
use TinyBlocks\Mapper\Internal\Strategies\ScalarMappingStrategy;
use TinyBlocks\Mapper\Internal\Transformers\DateTimeTransformer;
use TinyBlocks\Mapper\Internal\Transformers\EnumTransformer;
use TinyBlocks\Mapper\Internal\Transformers\ValueObjectUnwrapper;

final readonly class StrategyResolverFactory
{
    public function create(): StrategyResolver
    {
        $enumDetector = new EnumDetector();
        $scalarDetector = new ScalarDetector();
        $dateTimeDetector = new DateTimeDetector();
        $valueObjectDetector = new ValueObjectDetector();
        $collectibleDetector = new CollectibleDetector();

        $reflectionExtractor = new ReflectionExtractor();
        $valuePropertyExtractor = new ValuePropertyExtractor();

        $valueObjectUnwrapper = new ValueObjectUnwrapper(
            extractor: $valuePropertyExtractor,
            valueObjectDetector: $valueObjectDetector
        );

        $resolverContainer = new StrategyResolverContainer();

        $resolver = new StrategyResolver(
            new EnumMappingStrategy(
                detector: $enumDetector,
                transformer: new EnumTransformer()
            ),
            new ScalarMappingStrategy(detector: $scalarDetector),
            new DateTimeMappingStrategy(
                detector: $dateTimeDetector,
                transformer: new DateTimeTransformer()
            ),
            new CollectionMappingStrategy(
                detector: $collectibleDetector,
                resolverContainer: $resolverContainer
            ),
            new ComplexObjectMappingStrategy(
                extractor: $reflectionExtractor,
                unwrapper: $valueObjectUnwrapper,
                resolverContainer: $resolverContainer,
                valueObjectDetector: $valueObjectDetector
            )
        );

        $resolverContainer->set(resolver: $resolver);

        return $resolver;
    }
}
