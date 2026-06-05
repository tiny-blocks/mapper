<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use TinyBlocks\Mapper\ScalarCodec;

#[ScalarCodec(decode: 'fromText', encode: 'toText')]
#[ScalarCodec(decode: 'fromNumber', encode: 'toNumber')]
final readonly class Reference
{
    private function __construct(private string $value, private string $origin)
    {
    }

    public static function fromText(string $value): Reference
    {
        return new Reference(value: $value, origin: 'text');
    }

    public static function fromNumber(int $value): Reference
    {
        return new Reference(value: (string) $value, origin: 'number');
    }

    public function origin(): string
    {
        return $this->origin;
    }

    public function toText(): string
    {
        return $this->value;
    }

    public function toNumber(): int
    {
        return (int) $this->value;
    }
}
