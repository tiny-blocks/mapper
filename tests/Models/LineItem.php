<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class LineItem
{
    private function __construct(private Amount $amount, private string $reference)
    {
    }

    public static function of(Amount $amount, string $reference): LineItem
    {
        return new LineItem(amount: $amount, reference: strtoupper($reference));
    }

    public function amount(): Amount
    {
        return $this->amount;
    }

    public function reference(): string
    {
        return $this->reference;
    }
}
