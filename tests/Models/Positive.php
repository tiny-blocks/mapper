<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Positive
{
    private function __construct(private int $value)
    {
    }

    public static function from(int $value): Positive
    {
        return new Positive(value: $value);
    }

    public function value(): int
    {
        return $this->value;
    }
}
