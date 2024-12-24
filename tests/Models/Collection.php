<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Models;

use Closure;
use TinyBlocks\Collection\Collectible;
use TinyBlocks\Mapper\IterableMappability;
use TinyBlocks\Mapper\IterableMapper;
use Traversable;

final readonly class Collection implements Collectible, IterableMapper
{
    use IterableMappability;

    private iterable $iterator;

    private function __construct(iterable $iterator)
    {
        $this->iterator = $iterator;
    }

    public static function createFrom(iterable $elements): Collectible
    {
        return new Collection(iterator: $elements);
    }

    public static function createFromEmpty(): Collectible
    {
        // TODO: Implement createFromEmpty() method.
    }

    public function add(...$elements): Collectible
    {
        // TODO: Implement add() method.
    }

    public function contains(mixed $element): bool
    {
        // TODO: Implement contains() method.
    }

    public function count(): int
    {
        return iterator_count($this->iterator);
    }

    public function each(Closure ...$actions): Collectible
    {
        // TODO: Implement each() method.
    }

    public function equals(Collectible $other): bool
    {
        // TODO: Implement equals() method.
    }

    public function filter(?Closure ...$predicates): Collectible
    {
        // TODO: Implement filter() method.
    }

    public function findBy(Closure ...$predicates): mixed
    {
        // TODO: Implement findBy() method.
    }

    public function first(mixed $defaultValueIfNotFound = null): mixed
    {
        // TODO: Implement first() method.
    }

    public function flatten(): Collectible
    {
        // TODO: Implement flatten() method.
    }

    public function getBy(int $index, mixed $defaultValueIfNotFound = null): mixed
    {
        // TODO: Implement getBy() method.
    }

    public function getIterator(): Traversable
    {
        yield from $this->iterator;
    }

    public function groupBy(Closure $grouping): Collectible
    {
        // TODO: Implement groupBy() method.
    }

    public function isEmpty(): bool
    {
        // TODO: Implement isEmpty() method.
    }

    public function joinToString(string $separator): string
    {
        // TODO: Implement joinToString() method.
    }

    public function last(mixed $defaultValueIfNotFound = null): mixed
    {
        // TODO: Implement last() method.
    }

    public function map(Closure ...$transformations): Collectible
    {
        // TODO: Implement map() method.
    }

    public function remove(mixed $element): Collectible
    {
        // TODO: Implement remove() method.
    }

    public function removeAll(?Closure $filter = null): Collectible
    {
        // TODO: Implement removeAll() method.
    }

    public function reduce(Closure $aggregator, mixed $initial): mixed
    {
        // TODO: Implement reduce() method.
    }

    public function sort(
        \TinyBlocks\Collection\Order $order = \TinyBlocks\Collection\Order::ASCENDING_KEY,
        ?Closure $predicate = null
    ): Collectible {
        // TODO: Implement sort() method.
    }

    public function slice(int $index, int $length = -1): Collectible
    {
        // TODO: Implement slice() method.
    }
}
