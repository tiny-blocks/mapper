<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Camera
{
    public function __construct(public int $shotCount, public string $serialNumber)
    {
    }
}
