<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Charge
{
    public function __construct(public Amount $amount, public Refunds $refunds, public PaymentMethod $paymentMethod)
    {
    }
}
