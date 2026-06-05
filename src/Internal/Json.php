<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal;

use TinyBlocks\Mapper\Exceptions\UnmappableSource;

final class Json
{
    private const int ENCODE_FLAGS = JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION;

    private function __construct()
    {
    }

    public static function decode(string $payload): mixed
    {
        $decoded = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $template = 'Cannot decode JSON source: %s.';

            throw new UnmappableSource(message: sprintf($template, json_last_error_msg()));
        }

        return $decoded;
    }

    public static function encode(mixed $value): string
    {
        $encoded = json_encode($value, self::ENCODE_FLAGS);

        if ($encoded === false) {
            $template = 'Cannot encode value as JSON: %s.';

            throw new UnmappableSource(message: sprintf($template, json_last_error_msg()));
        }

        return $encoded;
    }
}
