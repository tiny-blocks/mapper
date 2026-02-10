<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Extractors;

use Traversable;

final readonly class IterableExtractor implements PropertyExtractor
{
    public function __construct(private ReflectionExtractor $extractor)
    {
    }

    public function extract(object $object): array
    {
        if ($object instanceof Traversable) {
            return iterator_to_array($object);
        }

        $properties = $this->extractor->extractProperties(object: $object);

        $candidates = array_filter(
            $properties,
            static fn(mixed $value): bool => is_array($value) || $value instanceof Traversable
        );

        $iterable = reset($candidates);

        return is_array($iterable) ? $iterable : iterator_to_array($iterable);
    }
}
