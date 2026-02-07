<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Transformers;

use BackedEnum;

final readonly class EnumTransformer implements Transformer
{
    public function transform(mixed $value): string|int
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return $value->name;
    }
}
