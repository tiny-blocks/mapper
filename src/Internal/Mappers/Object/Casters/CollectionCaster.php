<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

use TinyBlocks\Collection\Collectible;
use TinyBlocks\Mapper\Internal\Mappers\Object\ObjectMapper;
use TinyBlocks\Mapper\Internal\Mappers\Object\Reflector;
use TinyBlocks\Mapper\IterableMapper;

final readonly class CollectionCaster implements Caster
{
    public function __construct(private string $class)
    {
    }

    public function castValue(mixed $value): Collectible
    {
        $reflectionClass = Reflector::reflectFrom(class: $this->class);
        /** @var IterableMapper & Collectible $instance */
        $instance = $reflectionClass->newInstanceWithoutConstructor();

        $type = $instance->getType();

        if ($type === $this->class) {
            return $instance->createFrom(elements: $value);
        }

        $mapped = [];

        foreach ($value as $item) {
            $mapped[] = (new ObjectMapper())->map(iterable: $item, class: $type);
        }

        return $instance->createFrom(elements: $mapped);
    }
}
