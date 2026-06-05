<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappings\Layout;

use TinyBlocks\Mapper\Internal\Context;

final readonly class ColumnNode implements LayoutNode
{
    public function __construct(private string $column)
    {
    }

    public function read(array $row): mixed
    {
        return $row[$this->column] ?? null;
    }

    public function write(mixed $value, Context $context): array
    {
        return [$this->column => $context->write(value: $value)];
    }

    public function isPresentIn(array $row): bool
    {
        return array_key_exists($this->column, $row);
    }
}
