<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use TinyBlocks\Mapper\ObjectMappability;
use TinyBlocks\Mapper\ObjectMapper;

final readonly class ShippingAddress implements ObjectMapper
{
    use ObjectMappability;

    public function __construct(
        private string $city,
        private ShippingState $state,
        private string $street,
        private int $number,
        private ShippingCountry $country
    ) {
    }
}
