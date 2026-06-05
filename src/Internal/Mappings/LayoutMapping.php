<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappings;

use TinyBlocks\Mapper\Internal\Context;
use TinyBlocks\Mapper\Internal\Mappings\Layout\LayoutCodec;
use TinyBlocks\Mapper\Internal\Source;
use TinyBlocks\Mapper\Mapping;
use TinyBlocks\Mapper\MappingContext;

final readonly class LayoutMapping implements Mapping
{
    private LayoutCodec $codec;

    public function __construct(array $paths, private ?string $factory = null)
    {
        $this->codec = new LayoutCodec(paths: $paths);
    }

    public function read(mixed $source, MappingContext $context): object
    {
        $engineContext = Context::cast(context: $context);
        $normalized = Source::normalize(source: $source);
        $targetType = $engineContext->targetType();
        $nested = $this->codec->decode(row: $normalized, type: $targetType, naming: $engineContext->naming());

        return is_null($this->factory)
            ? $engineContext->reflectionRead(type: $targetType, source: $nested)
            : $engineContext->factoryRead(type: $targetType, method: $this->factory, source: $nested);
    }

    public function write(object $subject, MappingContext $context): mixed
    {
        $engineContext = Context::cast(context: $context);
        $targetType = $engineContext->targetType();

        return $this->codec->encode(
            type: $targetType,
            naming: $engineContext->naming(),
            context: $engineContext,
            instance: $subject
        );
    }
}
