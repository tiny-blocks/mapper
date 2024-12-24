<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Collection;

use ReflectionClass;
use TinyBlocks\Mapper\KeyPreservation;

final readonly class ArrayMapper
{
    public function map(mixed $value, KeyPreservation $keyPreservation): array
    {
        $mappedValues = [];
        $valueMapper = new ValueMapper();

        if ($valueMapper->valueIsCollectible(value: $value)) {
            $collectionMapper = new CollectionMapper(valueMapper: $valueMapper);
            return $collectionMapper->map(value: $value, keyPreservation: $keyPreservation);
        }

        $reflectionClass = new ReflectionClass($value);
        $shouldPreserveKeys = $keyPreservation->shouldPreserveKeys();

        foreach ($reflectionClass->getProperties() as $property) {
            $propertyValue = $property->getValue($value);

            $propertyValue = is_iterable($propertyValue)
                ? iterator_to_array($propertyValue, $shouldPreserveKeys)
                : $valueMapper->map(value: $propertyValue, keyPreservation: $keyPreservation);

            if (is_array($propertyValue)) {
                $arrayMapper = fn(mixed $value): mixed => $valueMapper->map(
                    value: $value,
                    keyPreservation: $keyPreservation
                );
                $propertyValue = array_map($arrayMapper, $propertyValue);
            }

            $mappedValues[$property->getName()] = $valueMapper->map(
                value: $propertyValue,
                keyPreservation: $keyPreservation
            );
        }

        return $shouldPreserveKeys ? $mappedValues : array_values($mappedValues);
    }
}
