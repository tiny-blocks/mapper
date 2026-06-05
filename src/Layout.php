<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use TinyBlocks\Mapper\Internal\Mappings\LayoutMapping;

/**
 * Builds a Mapping for a flattened relational source, declaring only the columns that deviate from the
 * prefix-derivation convention.
 *
 * <p>A property typed as an object expands into <code>{field}{separator}{subfield}</code> recursively, under
 * the active {@see NamingStrategy}. Columns that follow this convention are derived and need no entry. Only
 * renamed leaves, columns outside their expected prefix, and JSON-encoded columns are declared.</p>
 */
final class Layout
{
    private function __construct()
    {
    }

    /**
     * Creates a Mapping from the given path overrides.
     *
     * <p>When <code>$factory</code> is given, the reshaped graph is built through that public static factory on the
     * target type instead of reflection injection. The factory parameter names must match the target's property
     * names, and its parameters resolve through the same pipeline as a top-level {@see FactoryMethod} mapping.</p>
     *
     * @param array<string, string|JsonColumn> $paths Map of dot-notation graph path to a column name, or to a
     *                                                 {@see JsonColumn} for a column holding a JSON document.
     * @param string|null $factory The name of the public static factory used to build the target, or null to inject
     *                             through reflection.
     * @return Mapping The configured mapping.
     */
    public static function from(array $paths, ?string $factory = null): Mapping
    {
        return new LayoutMapping(paths: $paths, factory: $factory);
    }
}
