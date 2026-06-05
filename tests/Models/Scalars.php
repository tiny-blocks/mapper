<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Scalars
{
    public function __construct(public int $count, public string $label, public float $ratio, public bool $active)
    {
    }
}
