<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers;

final readonly class JsonMapper
{
    private const int JSON_FLAGS = JSON_THROW_ON_ERROR
    | JSON_UNESCAPED_UNICODE
    | JSON_PRESERVE_ZERO_FRACTION;

    public function map(array $value): string
    {
        return json_encode($value, self::JSON_FLAGS);
    }
}
