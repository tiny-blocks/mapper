<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Version
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): Version
    {
        $padded = array_pad(explode('.', $value), 2, '0');

        return new Version(value: implode('.', array_slice($padded, 0, 2)));
    }

    public function value(): string
    {
        return $this->value;
    }
}
