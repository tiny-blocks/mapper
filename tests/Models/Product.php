<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use ArrayIterator;
use DateTimeImmutable;
use TinyBlocks\Mapper\ObjectMappability;
use TinyBlocks\Mapper\ObjectMapper;

final readonly class Product implements ObjectMapper
{
    use ObjectMappability;

    public function __construct(
        public int $id,
        public Amount $amount,
        public Description $description,
        public ArrayIterator $attributes,
        public array $inventory,
        public ProductStatus $status,
        public DateTimeImmutable $createdAt = new DateTimeImmutable()
    ) {
    }
}
