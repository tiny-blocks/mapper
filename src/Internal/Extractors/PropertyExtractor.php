<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Extractors;

/**
 * Defines the contract for extracting values from objects.
 */
interface PropertyExtractor
{
    /**
     * Extracts a value from an object.
     *
     * @param object $object The object to extract from.
     * @return mixed The extracted value.
     */
    public function extract(object $object): mixed;
}
