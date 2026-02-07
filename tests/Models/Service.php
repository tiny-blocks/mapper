<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use Closure;
use TinyBlocks\Mapper\ObjectMappability;
use TinyBlocks\Mapper\ObjectMapper;

final readonly class Service implements ObjectMapper
{
    use ObjectMappability;

    public function __construct(public Closure $action)
    {
    }
}
