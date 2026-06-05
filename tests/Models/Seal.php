<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use TinyBlocks\Mapper\ScalarCodec;

#[ScalarCodec(decode: 'fromToken', encode: 'toToken')]
final readonly class Seal
{
    private function __construct(private Sku $sku)
    {
    }

    public static function fromToken(string $token): Seal
    {
        return new Seal(sku: Sku::from(value: $token));
    }

    public function toToken(): string
    {
        $template = 'seal:%s';

        return sprintf($template, $this->sku->value());
    }
}
