<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Customer
{
    public function __construct(public Address $billing, public string $identifier)
    {
    }
}
