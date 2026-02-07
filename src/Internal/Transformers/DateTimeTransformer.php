<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Transformers;

final readonly class DateTimeTransformer implements Transformer
{
    private const string ISO8601_FORMAT = 'c';

    public function transform(mixed $value): string
    {
        return $value->format(format: self::ISO8601_FORMAT);
    }
}
