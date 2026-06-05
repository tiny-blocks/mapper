<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Metadata;

final readonly class ScalarCodecBinding
{
    public function __construct(public string $decode, public string $encode, public string $decodeType)
    {
    }
}
