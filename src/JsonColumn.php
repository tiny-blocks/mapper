<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

/**
 * A flattened column that holds a JSON document mapped onto a nested graph path.
 */
final readonly class JsonColumn
{
    public function __construct(public string $column)
    {
    }
}
