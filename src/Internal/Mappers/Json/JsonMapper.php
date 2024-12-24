<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Json;

final readonly class JsonMapper
{
    public function map(array $value): string
    {
        $isAllEmpty = static function (array $items): bool {
            return array_reduce($items, static fn(bool $carry, mixed $item): bool => $carry && empty($item), true);
        };

        if ($isAllEmpty(items: $value)) {
            return '[]';
        }

        return json_encode($value, JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE);
    }
}
