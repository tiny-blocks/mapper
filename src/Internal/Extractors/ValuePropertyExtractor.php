<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Extractors;

use ReflectionClass;

final readonly class ValuePropertyExtractor implements PropertyExtractor
{
    private const string VALUE_PROPERTY = 'value';

    public function extract(object $object): mixed
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty(self::VALUE_PROPERTY);

        return $property->getValue($object);
    }
}
