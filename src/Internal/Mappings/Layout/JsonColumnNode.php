<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappings\Layout;

use TinyBlocks\Mapper\Internal\Context;
use TinyBlocks\Mapper\Internal\Json;

final readonly class JsonColumnNode implements LayoutNode
{
    public function __construct(private string $column)
    {
    }

    public function read(array $row): mixed
    {
        $raw = $row[$this->column] ?? null;

        return is_string($raw) ? Json::decode(payload: $raw) : $raw;
    }

    public function write(mixed $value, Context $context): array
    {
        $serialized = $context->write(value: $value);

        return [$this->column => Json::encode(value: $serialized)];
    }

    public function isPresentIn(array $row): bool
    {
        return array_key_exists($this->column, $row);
    }
}
