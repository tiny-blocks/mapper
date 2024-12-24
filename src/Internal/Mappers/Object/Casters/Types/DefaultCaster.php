<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters\Types;

use ReflectionProperty;
use TinyBlocks\Mapper\Internal\Mappers\Object\Casters\TypeCaster;
use TinyBlocks\Mapper\Internal\Mappers\Object\ObjectMapper;

final readonly class DefaultCaster implements TypeCaster
{
    private const int DOCBLOCK_PROPERTY_TYPE = 1;
    private const string DOCBLOCK_PROPERTY_TYPE_PATTERN = '/@var\s+([A-Za-z0-9\\\[\]]+)\s+\$%s/';

    public function __construct(public ReflectionProperty $property)
    {
    }

    public function applyCast(mixed $value): mixed
    {
        if (!is_iterable($value)) {
            return $value;
        }

        $comment = $this->property->getDocComment();
        $objectMapper = new ObjectMapper();

        if ($comment !== false) {
            $mapped = [];
            $pattern = sprintf(self::DOCBLOCK_PROPERTY_TYPE_PATTERN, $this->property->getName());

            preg_match($pattern, $comment, $matches);

            if (isset($matches[self::DOCBLOCK_PROPERTY_TYPE])) {
                $class = rtrim($matches[self::DOCBLOCK_PROPERTY_TYPE], '[]');

                foreach ($value as $item) {
                    $mapped[] = $objectMapper->map(iterable: $item, class: $class);
                }
            }

            return $mapped;
        }

        return $objectMapper->map(iterable: $value, class: $this->property->getType()->getName());
    }
}
