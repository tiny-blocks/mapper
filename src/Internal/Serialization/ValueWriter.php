<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Serialization;

use TinyBlocks\Mapper\Configuration;
use TinyBlocks\Mapper\Internal\Context;
use TinyBlocks\Mapper\Internal\Engine;
use TinyBlocks\Mapper\Internal\Mappings\MappingRegistry;
use TinyBlocks\Mapper\Internal\Metadata\ClassDescriptor;
use TinyBlocks\Mapper\Internal\Metadata\Descriptors;
use TinyBlocks\Mapper\Internal\Serialization\Encoders\Encoder;
use TinyBlocks\Mapper\Internal\Serialization\Encoders\EncoderFactory;
use TinyBlocks\Mapper\NamingStrategy;
use Traversable;

final readonly class ValueWriter
{
    private array $encoders;

    public function __construct(
        private Engine $engine,
        private NamingStrategy $naming,
        private MappingRegistry $registry
    ) {
        $this->encoders = EncoderFactory::build();
    }

    public function write(mixed $value, Configuration $configuration): mixed
    {
        if (is_object($value)) {
            return $this->writeObject(value: $value, configuration: $configuration);
        }

        if (is_array($value)) {
            return $this->serializeEach(values: $value, configuration: $configuration);
        }

        return $value;
    }

    public function serialize(object $subject, Configuration $configuration): mixed
    {
        $descriptor = Descriptors::of(type: $subject::class);

        foreach ($this->encoders as $encoder) {
            assert($encoder instanceof Encoder);

            if ($encoder->supports(subject: $subject, descriptor: $descriptor)) {
                return $encoder->encode(subject: $subject, descriptor: $descriptor);
            }
        }

        return $subject instanceof Traversable
            ? $this->serializeEach(values: $subject, configuration: $configuration)
            : $this->serializeObject(subject: $subject, descriptor: $descriptor, configuration: $configuration);
    }

    private function writeObject(object $value, Configuration $configuration): mixed
    {
        $registered = $this->registry->find(type: $value::class);

        if (!is_null($registered)) {
            return $registered->mapping->write(
                subject: $value,
                context: new Context(
                    engine: $this->engine,
                    targetType: $registered->registeredType,
                    configuration: $configuration
                )
            );
        }

        return $this->serialize(subject: $value, configuration: $configuration);
    }

    private function serializeEach(iterable $values, Configuration $configuration): array
    {
        $serialized = [];

        foreach ($values as $key => $element) {
            $serialized[$key] = $this->write(value: $element, configuration: $configuration);
        }

        return $configuration->arrangeKeys(values: $serialized);
    }

    public function reflectionWrite(object $subject, ClassDescriptor $descriptor, Configuration $configuration): array
    {
        $serialized = [];

        foreach ($descriptor->declaredProperties as $name => $property) {
            if ($configuration->omits(field: $name)) {
                continue;
            }

            $key = $this->naming->toSourceKey(propertyName: $name);
            $serialized[$key] = $this->write(
                value: $property->getValue($subject),
                configuration: $configuration
            );
        }

        return $serialized;
    }

    private function serializeObject(object $subject, ClassDescriptor $descriptor, Configuration $configuration): mixed
    {
        $single = $descriptor->singleProperty;

        if (!is_null($single) && !$descriptor->isDecomposable) {
            return $this->write(value: $single->getValue($subject), configuration: $configuration);
        }

        return $this->reflectionWrite(subject: $subject, descriptor: $descriptor, configuration: $configuration);
    }
}
