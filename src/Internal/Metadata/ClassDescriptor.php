<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Metadata;

use ReflectionClass;
use ReflectionProperty;

final readonly class ClassDescriptor
{
    public function __construct(
        public Kind $kind,
        public string $type,
        public ?ReflectionClass $reflection,
        public ?string $elementType,
        public array $scalarCodecs,
        public bool $isDecomposable,
        public ?ReflectionProperty $singleProperty,
        public array $declaredProperties,
        public bool $isReducibleToScalar
    ) {
    }

    public function newInstance(): object
    {
        return $this->reflection->newInstanceWithoutConstructor();
    }

    public function hasScalarCodec(): bool
    {
        return $this->scalarCodecs !== [];
    }
}
