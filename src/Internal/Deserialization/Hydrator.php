<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Deserialization;

use TinyBlocks\Mapper\Exceptions\UnexpectedKey;
use TinyBlocks\Mapper\Internal\Metadata\ClassDescriptor;
use TinyBlocks\Mapper\NamingStrategy;

final readonly class Hydrator
{
    public function __construct(
        private NamingStrategy $naming,
        private ValueReader $valueReader,
        private bool $rejectUnknownKeys
    ) {
    }

    public function build(array $source, ClassDescriptor $descriptor): object
    {
        $instance = $descriptor->newInstance();
        $expectedKeys = [];

        foreach ($descriptor->declaredProperties as $name => $property) {
            $key = $this->naming->toSourceKey(propertyName: $name);
            $expectedKeys[$key] = $name;

            if (!array_key_exists($key, $source)) {
                continue;
            }

            $resolved = $this->valueReader->resolve(value: $source[$key], type: $property->getType());
            $property->setValue($instance, $resolved);
        }

        if (!$this->rejectUnknownKeys) {
            return $instance;
        }

        foreach (array_keys($source) as $key) {
            if (array_key_exists($key, $expectedKeys)) {
                continue;
            }

            $template = 'Unknown source key "%s" while mapping %s.';

            throw new UnexpectedKey(message: sprintf($template, (string)$key, $descriptor->type));
        }

        return $instance;
    }
}
