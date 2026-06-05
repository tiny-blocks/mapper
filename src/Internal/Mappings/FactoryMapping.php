<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappings;

use TinyBlocks\Mapper\Internal\Context;
use TinyBlocks\Mapper\Mapping;
use TinyBlocks\Mapper\MappingContext;

final readonly class FactoryMapping implements Mapping
{
    public function __construct(private string $method)
    {
    }

    public function read(mixed $source, MappingContext $context): object
    {
        $engineContext = Context::cast(context: $context);

        return $engineContext->factoryRead(type: $engineContext->targetType(), method: $this->method, source: $source);
    }

    public function write(object $subject, MappingContext $context): mixed
    {
        return Context::cast(context: $context)->serialize(subject: $subject);
    }
}
