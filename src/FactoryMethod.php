<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use TinyBlocks\Mapper\Internal\Mappings\FactoryMapping;

/**
 * Builds a Mapping that constructs the target through one of its own public static factory methods.
 *
 * <p>This complements {@see Codec}: where a codec converts a scalar through consumer-supplied closures, a factory
 * mapping is reflection-based and works for any arity. The mapped type imports nothing from the library. On read,
 * the factory drives the type's real construction path (invariants, lookups, parsing), which plain reflection
 * injection would skip.</p>
 */
final class FactoryMethod
{
    private function __construct()
    {
    }

    /**
     * Creates a Mapping that builds the target by invoking the named public static factory.
     *
     * <p>Each factory parameter is resolved from the source by its name under the active {@see NamingStrategy}, with
     * scalar coercion and recursive mapping. Nested objects, enums, and date-times resolve through the same pipeline,
     * honoring any registered mapping. The factory parameter names must match the target's property names.</p>
     *
     * <ul>
     *   <li>A single-parameter factory is fed the scalar source directly.</li>
     *   <li>A multi-parameter factory is fed an array keyed by parameter name. A top-level multi-parameter source
     *       must be an array, not a JSON string.</li>
     * </ul>
     *
     * <p>Writing is reflection over the instance's declared properties, not the inverse of the factory. A
     * single-property object writes back to a scalar and a compound one to an array, so the round-trip is lossless
     * only when the persisted form is the canonical form the factory consumes.</p>
     *
     * @param string $method The name of the public static factory method on the target type.
     * @return Mapping The configured mapping.
     */
    public static function using(string $method): Mapping
    {
        return new FactoryMapping(method: $method);
    }
}
