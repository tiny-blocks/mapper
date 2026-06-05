<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappings;

use Closure;
use TinyBlocks\Mapper\Exceptions\InvalidSubtypeCase;
use TinyBlocks\Mapper\Exceptions\UnknownSubtype;
use TinyBlocks\Mapper\Internal\Context;
use TinyBlocks\Mapper\Internal\Source;
use TinyBlocks\Mapper\Mapping;
use TinyBlocks\Mapper\MappingContext;

final readonly class SubtypeMapping implements Mapping
{
    public function __construct(private array $cases, private string $field, private ?Closure $default)
    {
    }

    public function read(mixed $source, MappingContext $context): object
    {
        $engineContext = Context::cast(context: $context);
        $normalized = Source::normalize(source: $source);
        $value = $normalized[$this->field] ?? null;
        $concrete = is_string($value) ? ($this->cases[$value] ?? null) : null;

        if (is_null($concrete)) {
            if (!is_null($this->default)) {
                return ($this->default)();
            }

            $template = 'No subtype case matches value "%s" for field "%s".';

            throw new UnknownSubtype(message: sprintf($template, (string) $value, $this->field));
        }

        return $engineContext->reflectionRead(type: $concrete, source: $normalized);
    }

    public function write(object $subject, MappingContext $context): mixed
    {
        $engineContext = Context::cast(context: $context);
        $serialized = $engineContext->reflectionWrite(subject: $subject);
        $caseValue = array_find_key($this->cases, static fn(mixed $caseClass): bool => $subject instanceof $caseClass);

        if (is_null($caseValue)) {
            $template = 'No subtype case matches concrete class %s for field "%s".';

            throw new UnknownSubtype(message: sprintf($template, $subject::class, $this->field));
        }

        $serialized[$this->field] = $caseValue;

        return $serialized;
    }

    public function ensureCasesAreSubtypesOf(string $root): void
    {
        foreach ($this->cases as $value => $class) {
            if (!is_a($class, $root, true)) {
                $template = 'Subtype case "%s" maps %s, which is not a subtype of %s.';

                throw new InvalidSubtypeCase(message: sprintf($template, $value, $class, $root));
            }
        }
    }
}
