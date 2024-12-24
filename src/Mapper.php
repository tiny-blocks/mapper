<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

/**
 * Defines methods for converting objects or collections of objects to JSON and arrays.
 */
interface Mapper
{
    /**
     * Converts the object or collection to a JSON string.
     *
     * The behavior of key preservation:
     *  - {@see KeyPreservation::DISCARD}: Keys will be discarded during conversion.
     *  - {@see KeyPreservation::PRESERVE}: Keys will be preserved in the resulting JSON.
     *
     * By default, `KeyPreservation::PRESERVE` is used, meaning keys will be preserved.
     *
     * @param KeyPreservation $keyPreservation The strategy for handling keys during conversion.
     *                                         Determines whether to preserve or discard keys.
     *
     * @return string The JSON representation of the object or collection.
     */
    public function toJson(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): string;

    /**
     * Converts the object or collection to an array.
     *
     * The behavior of key preservation:
     *  - {@see KeyPreservation::DISCARD}: Keys will be discarded during conversion.
     *  - {@see KeyPreservation::PRESERVE}: Keys will be preserved in the resulting array.
     *
     * By default, `KeyPreservation::PRESERVE` is used, meaning keys will be preserved.
     *
     * @param KeyPreservation $keyPreservation The strategy for handling keys during conversion.
     *                                         Determines whether to preserve or discard keys.
     *
     * @return array The array representation of the object or collection.
     */
    public function toArray(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): array;
}
