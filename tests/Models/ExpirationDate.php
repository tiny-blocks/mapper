<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Models;

use DateTimeImmutable;
use TinyBlocks\Mapper\ObjectMappability;
use TinyBlocks\Mapper\ObjectMapper;

final readonly class ExpirationDate implements ObjectMapper
{
    use ObjectMappability;

    public function __construct(private DateTimeImmutable $value)
    {
    }
}
