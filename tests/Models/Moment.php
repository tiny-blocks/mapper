<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use DateTimeImmutable;

final readonly class Moment
{
    public function __construct(public DateTimeImmutable $at)
    {
    }
}
