<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use TinyBlocks\Mapper\ObjectMappability;
use TinyBlocks\Mapper\ObjectMapper;

final readonly class Dragon implements ObjectMapper
{
    use ObjectMappability;

    public function __construct(
        private string $name,
        private DragonType $type,
        private float $power,
        private array $skills
    ) {
    }
}
