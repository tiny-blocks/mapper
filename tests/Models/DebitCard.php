<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class DebitCard extends PaymentMethod
{
    public function __construct(public string $cardNumber)
    {
    }
}
