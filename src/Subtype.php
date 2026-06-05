<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use Closure;
use ReflectionClass;
use TinyBlocks\Mapper\Exceptions\InvalidSubtypeCase;
use TinyBlocks\Mapper\Internal\Mappings\SubtypeMapping;

/**
 * Builds a Mapping that selects a concrete type by the value of a subtype field.
 */
final class Subtype
{
    private function __construct()
    {
    }

    /**
     * Creates a Mapping that selects a concrete type by a field value derived from each type's short name.
     *
     * <p>The subtype value of each type is derived by applying the naming strategy to its short name, so the
     * discriminator follows the same convention as the keys. On read, the field value selects the concrete
     * class. On write, the concrete class is reverse-looked-up to its derived value.</p>
     *
     * @param string $field The source field holding the subtype value.
     * @param list<class-string> $types The concrete classes, whose short names derive the field values.
     * @param NamingStrategy|null $naming The convention used to derive values from short names. Defaults to snake_case.
     * @param Closure|null $default Factory used when no case matches. Signature: <code>fn(): object</code>.
     * @return Mapping The configured mapping.
     * @throws InvalidSubtypeCase When two types derive the same subtype value.
     */
    public static function by(
        string $field,
        array $types,
        ?NamingStrategy $naming = null,
        ?Closure $default = null
    ): Mapping {
        $strategy = $naming ?? SnakeCase::create();
        $cases = [];

        foreach ($types as $type) {
            $value = $strategy->toSourceKey(propertyName: new ReflectionClass(objectOrClass: $type)->getShortName());

            if (array_key_exists($value, $cases)) {
                $template = 'Subtype value "%s" is derived from more than one type (%s and %s).';

                throw new InvalidSubtypeCase(message: sprintf($template, $value, $cases[$value], $type));
            }

            $cases[$value] = $type;
        }

        return new SubtypeMapping(cases: $cases, field: $field, default: $default);
    }
}
