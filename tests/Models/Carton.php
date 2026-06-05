<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Carton
{
    public function __construct(public Reel $reel)
    {
    }
}
