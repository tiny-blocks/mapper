<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappings\Layout;

use TinyBlocks\Mapper\Internal\Context;

/**
 * Reads from a flat row or writes back into one for a single position of the layout tree.
 */
interface LayoutNode
{
    /**
     * Reads the value backing this position from the flat row.
     *
     * @param array<int|string, mixed> $row The flat input row.
     * @return mixed The value resolved at this position, either a leaf or a nested map.
     */
    public function read(array $row): mixed;

    /**
     * Writes the value backing this position into one or more flat columns.
     *
     * @param mixed $value The instance-side value to serialize.
     * @param Context $context The engine's internal context.
     * @return array<string, mixed> The flat key-value pairs to merge into the flat row.
     */
    public function write(mixed $value, Context $context): array;

    /**
     * Tells whether at least one column backing this position exists in the flat row.
     *
     * @param array<int|string, mixed> $row The flat input row.
     * @return bool True when a backing column is present, false otherwise.
     */
    public function isPresentIn(array $row): bool;
}
