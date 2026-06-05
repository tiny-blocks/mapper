<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Ticket
{
    public function __construct(public Reference $reference)
    {
    }
}
