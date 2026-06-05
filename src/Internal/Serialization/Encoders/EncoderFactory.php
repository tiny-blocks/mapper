<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Serialization\Encoders;

final class EncoderFactory
{
    private function __construct()
    {
    }

    public static function build(): array
    {
        return [
            new BackedEnumEncoder(),
            new PureEnumEncoder(),
            new DateTimeEncoder(),
            new ScalarCodecEncoder()
        ];
    }
}
