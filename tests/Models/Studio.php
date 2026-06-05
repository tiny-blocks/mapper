<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Studio
{
    public function __construct(public string $tag, public Camera $mainCamera)
    {
    }
}
