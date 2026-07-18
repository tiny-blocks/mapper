<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use TinyBlocks\Mapper\Transient;

final readonly class WithTransient
{
    public function __construct(
        public string $name,
        public int $amount,
        #[Transient]
        public string $checksum,
        public string $reference
    ) {
    }
}
