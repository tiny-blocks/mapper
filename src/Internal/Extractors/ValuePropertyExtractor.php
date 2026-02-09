<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Extractors;

use ReflectionClass;

final readonly class ValuePropertyExtractor implements PropertyExtractor
{
    public function extract(object $object): mixed
    {
        return new ReflectionClass($object)->getProperties()[0]->getValue($object);
    }
}
