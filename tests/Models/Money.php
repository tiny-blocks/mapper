<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Money
{
    private function __construct(private Positive $amount, private Currency $currency)
    {
    }

    public static function of(Positive $amount, Currency $currency): Money
    {
        return new Money(amount: $amount, currency: $currency);
    }

    public function amount(): Positive
    {
        return $this->amount;
    }

    public function currency(): Currency
    {
        return $this->currency;
    }
}
