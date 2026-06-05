<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use DateTimeImmutable;

final readonly class Profile
{
    public function __construct(
        public string $name,
        public ?string $title,
        public Severity $severity,
        public DateTimeImmutable $createdAt
    ) {
    }
}
