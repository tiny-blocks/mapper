<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class MemberId
{
    public function __construct(private string $value)
    {
    }

    public function value(): string
    {
        return $this->value;
    }
}
