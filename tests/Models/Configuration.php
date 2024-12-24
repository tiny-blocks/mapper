<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Models;

use Generator;
use TinyBlocks\Mapper\ObjectMappability;
use TinyBlocks\Mapper\ObjectMapper;

final readonly class Configuration implements ObjectMapper
{
    use ObjectMappability;

    public function __construct(private Generator $id, private Generator $options)
    {
    }
}
