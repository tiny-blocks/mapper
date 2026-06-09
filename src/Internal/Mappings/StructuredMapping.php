<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappings;

use TinyBlocks\Mapper\Internal\Context;
use TinyBlocks\Mapper\Internal\Source;
use TinyBlocks\Mapper\Mapping;
use TinyBlocks\Mapper\MappingContext;

final readonly class StructuredMapping implements Mapping
{
    public function read(mixed $source, MappingContext $context): object
    {
        $engineContext = Context::cast(context: $context);

        return $engineContext->reflectionRead(
            type: $engineContext->targetType(),
            source: Source::normalize(source: $source)
        );
    }

    public function write(object $subject, MappingContext $context): mixed
    {
        return Context::cast(context: $context)->reflectionWrite(subject: $subject);
    }
}
