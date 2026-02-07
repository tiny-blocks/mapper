<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use TinyBlocks\Mapper\ObjectMappability;
use TinyBlocks\Mapper\ObjectMapper;

final readonly class Amount implements ObjectMapper
{
    use ObjectMappability;

    private function __construct(public float $value, public Currency $currency)
    {
    }

    public static function from(float $value, Currency $currency): Amount
    {
        return new Amount(value: $value, currency: $currency);
    }
}
