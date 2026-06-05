<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Metadata;

final class Descriptors
{
    private static array $cache = [];

    private function __construct()
    {
    }

    public static function of(string $type): ClassDescriptor
    {
        return Descriptors::$cache[$type] ??= DescriptorFactory::build(type: $type);
    }
}
