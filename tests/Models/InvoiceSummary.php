<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Models;

final readonly class InvoiceSummary
{
    public function __construct(public float $amount, public string $customer)
    {
    }
}
