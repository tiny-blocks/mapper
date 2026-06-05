<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Account
{
    public function __construct(public string $accountId, public Contact $primaryContact)
    {
    }
}
