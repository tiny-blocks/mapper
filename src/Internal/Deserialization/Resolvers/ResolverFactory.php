<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Deserialization\Resolvers;

use TinyBlocks\Mapper\Internal\Deserialization\ValueReader;
use TinyBlocks\Mapper\Internal\Engine;

final class ResolverFactory
{
    private function __construct()
    {
    }

    public static function build(Engine $engine, ValueReader $reader): ResolverChain
    {
        return new ResolverChain(
            new BackedEnumResolver(),
            new PureEnumResolver(),
            new DateTimeResolver(),
            new ScalarCodecResolver(),
            new NestedResolver(engine: $engine),
            new SinglePropertyResolver(reader: $reader)
        );
    }
}
