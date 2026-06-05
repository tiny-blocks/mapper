<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappings\Layout;

use TinyBlocks\Mapper\Internal\Context;
use TinyBlocks\Mapper\Internal\Deserialization\ValueReader;

final readonly class NestedNode implements LayoutNode
{
    private array $children;

    public function __construct(LayoutChild ...$children)
    {
        $this->children = $children;
    }

    public function read(array $row): array
    {
        $nested = [];

        foreach ($this->children as $child) {
            $nested[$child->sourceKey] = $child->node->read(row: $row);
        }

        return $nested;
    }

    public function write(mixed $value, Context $context): array
    {
        $flat = [];

        foreach ($this->children as $child) {
            $property = $child->property;
            $extracted = $property->isInitialized($value) ? $property->getValue($value) : null;

            foreach ($child->node->write(value: $extracted, context: $context) as $key => $entry) {
                $flat[$key] = $entry;
            }
        }

        return $flat;
    }

    public function arguments(array $row, ValueReader $valueReader): array
    {
        $arguments = [];

        foreach ($this->children as $child) {
            if (!$child->node->isPresentIn(row: $row)) {
                continue;
            }

            $arguments[$child->property->getName()] = $valueReader->resolve(
                value: $child->node->read(row: $row),
                type: $child->property->getType()
            );
        }

        return $arguments;
    }

    public function isPresentIn(array $row): bool
    {
        return array_any($this->children, static fn(LayoutChild $child): bool => $child->node->isPresentIn(row: $row));
    }
}
