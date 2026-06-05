<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Cpf extends TaxId
{
    private function __construct(private string $digits)
    {
    }

    public static function from(string $digits): Cpf
    {
        return new Cpf(digits: $digits);
    }

    public function digits(): string
    {
        return $this->digits;
    }
}
