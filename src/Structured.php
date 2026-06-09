<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use TinyBlocks\Mapper\Internal\Mappings\StructuredMapping;

/**
 * Builds a Mapping that preserves a single-property type's object shape instead of collapsing it to a scalar.
 */
final class Structured
{
    private function __construct()
    {
    }

    /**
     * Creates a Mapping that serializes and deserializes the type by reflection, keeping its object shape.
     *
     * <p>By default, a single-property value object collapses to its inner scalar on write. Registering this
     * mapping emits the property as an object on write and rebuilds it by reflection on read, so the shape
     * survives the round trip. It is the {@see Subtype} mapping without the discriminator field.</p>
     *
     * @return Mapping The configured mapping.
     */
    public static function create(): Mapping
    {
        return new StructuredMapping();
    }
}
