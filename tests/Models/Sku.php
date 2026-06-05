<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Sku
{
    private function __construct(private string $value)
    {
    }

    public static function from(string $value): Sku
    {
        return new Sku(value: strtoupper($value));
    }

    public function value(): string
    {
        return $this->value;
    }
}
