<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Collection;

use TinyBlocks\Collection\Collectible;
use TinyBlocks\Mapper\KeyPreservation;

final readonly class CollectionMapper
{
    public function __construct(private ValueMapper $valueMapper)
    {
    }

    public function map(Collectible $value, KeyPreservation $keyPreservation): array
    {
        $mappedValues = [];

        foreach ($value as $key => $element) {
            $mappedValues[$key] = $this->valueMapper->map(value: $element, keyPreservation: $keyPreservation);
        }

        return $mappedValues;
    }
}
