<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Pix extends PaymentMethod
{
    public function __construct(public string $payerId)
    {
    }

    public static function pending(): Pix
    {
        return new Pix(payerId: 'pending');
    }
}
