<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Cnpj extends TaxId
{
    private function __construct(private string $base, private string $branch)
    {
    }

    public static function of(string $base, string $branch): Cnpj
    {
        return new Cnpj(base: $base, branch: $branch);
    }

    public function base(): string
    {
        return $this->base;
    }

    public function branch(): string
    {
        return $this->branch;
    }
}
