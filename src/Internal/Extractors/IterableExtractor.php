<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Extractors;

use TinyBlocks\Mapper\IterableMapper;
use Traversable;

final readonly class IterableExtractor implements PropertyExtractor
{
    public function __construct(private ReflectionExtractor $extractor)
    {
    }

    public function extract(object $object): array
    {
        if ($object instanceof IterableMapper) {
            $iterable = $this->fromMapper(mapper: $object);

            if ($iterable !== null) {
                return match (true) {
                    is_array($iterable) => $iterable,
                    $iterable instanceof Traversable => iterator_to_array($iterable),
                };
            }
        }

        if ($object instanceof Traversable) {
            return iterator_to_array($object);
        }

        return [];
    }

    private function fromMapper(IterableMapper $mapper): ?iterable
    {
        if ($mapper instanceof Traversable) {
            return $mapper;
        }

        if (method_exists($mapper, 'getIterator')) {
            return $mapper->getIterator();
        }

        return $this->fromProperties(mapper: $mapper);
    }

    private function fromProperties(IterableMapper $mapper): ?iterable
    {
        $properties = $this->extractor->extractProperties(object: $mapper);

        $elements = $properties['elements'] ?? null;

        if (is_array($elements) || $elements instanceof Traversable) {
            return $elements;
        }

        $candidates = array_filter(
            $properties,
            static fn(mixed $value): bool => is_array($value) || $value instanceof Traversable
        );

        return count($candidates) === 1
            ? reset($candidates)
            : null;
    }
}
