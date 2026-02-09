<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use DateTimeImmutable;
use Generator;
use TinyBlocks\Mapper\ObjectMappability;
use TinyBlocks\Mapper\ObjectMapper;

final readonly class Order implements ObjectMapper
{
    use ObjectMappability;

    public function __construct(public Uuid $id, public Generator $items, public DateTimeImmutable $createdAt)
    {
    }
}
