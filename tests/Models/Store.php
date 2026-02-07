<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use TinyBlocks\Mapper\ObjectMappability;
use TinyBlocks\Mapper\ObjectMapper;

final readonly class Store implements ObjectMapper
{
    use ObjectMappability;

    public function __construct(
        public string $id,
        public string $name,
        public bool $active
    ) {
    }
}
