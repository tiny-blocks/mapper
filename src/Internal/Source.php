<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal;

use TinyBlocks\Mapper\Exceptions\UnmappableSource;

final class Source
{
    private function __construct()
    {
    }

    public static function normalize(mixed $source): array
    {
        if (is_string($source)) {
            $decoded = Json::decode(payload: $source);

            if (!is_array($decoded)) {
                $template = 'Decoded JSON is not an array, got %s.';

                throw new UnmappableSource(message: sprintf($template, get_debug_type($decoded)));
            }

            return $decoded;
        }

        return is_array($source) ? $source : iterator_to_array($source);
    }
}
