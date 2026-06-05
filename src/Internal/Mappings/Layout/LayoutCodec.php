<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappings\Layout;

use ReflectionProperty;
use WeakMap;
use TinyBlocks\Mapper\Internal\Context;
use TinyBlocks\Mapper\Internal\Deserialization\ValueReader;
use TinyBlocks\Mapper\Internal\Metadata\Descriptors;
use TinyBlocks\Mapper\Internal\Metadata\ShapeAnalyzer;
use TinyBlocks\Mapper\JsonColumn;
use TinyBlocks\Mapper\NamingStrategy;

final class LayoutCodec
{
    /** @var WeakMap<NamingStrategy, array<string, NestedNode>> */
    private WeakMap $trees;

    private readonly array $overrides;

    public function __construct(array $paths)
    {
        $this->trees = new WeakMap();
        $this->overrides = array_map(
            static fn(mixed $entry): LayoutNode => $entry instanceof JsonColumn
                ? new JsonColumnNode(column: $entry->column)
                : new ColumnNode(column: $entry),
            $paths
        );
    }

    private function build(string $type, NamingStrategy $naming, array $prefix): NestedNode
    {
        $children = [];

        foreach (Descriptors::of(type: $type)->declaredProperties as $name => $property) {
            $path = [...$prefix, $name];
            $children[] = new LayoutChild(
                node: $this->resolve(path: $path, naming: $naming, property: $property),
                property: $property,
                sourceKey: $naming->toSourceKey(propertyName: $name)
            );
        }

        return new NestedNode(...$children);
    }

    public function decode(array $row, string $type, NamingStrategy $naming): array
    {
        return $this->treeFor(type: $type, naming: $naming)->read(row: $row);
    }

    public function encode(string $type, NamingStrategy $naming, Context $context, object $instance): array
    {
        return $this->treeFor(type: $type, naming: $naming)->write(value: $instance, context: $context);
    }

    private function resolve(array $path, NamingStrategy $naming, ReflectionProperty $property): LayoutNode
    {
        $pathKey = implode('.', $path);

        if (array_key_exists($pathKey, $this->overrides)) {
            return $this->overrides[$pathKey];
        }

        $propertyClass = ShapeAnalyzer::decomposableTypeOf(property: $property);

        if (!is_null($propertyClass)) {
            return $this->build(type: $propertyClass, naming: $naming, prefix: $path);
        }

        return new ColumnNode(column: $naming->derivedColumn(segments: $path));
    }

    private function treeFor(string $type, NamingStrategy $naming): NestedNode
    {
        $cached = $this->trees[$naming] ?? [];

        if (!array_key_exists($type, $cached)) {
            $cached[$type] = $this->build(type: $type, naming: $naming, prefix: []);
            $this->trees[$naming] = $cached;
        }

        return $cached[$type];
    }

    public function arguments(array $row, string $type, NamingStrategy $naming, ValueReader $valueReader): array
    {
        return $this->treeFor(type: $type, naming: $naming)->arguments(row: $row, valueReader: $valueReader);
    }
}
