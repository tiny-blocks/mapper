<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal;

use TinyBlocks\Mapper\Configuration;
use TinyBlocks\Mapper\Internal\Exceptions\ForeignMappingContext;
use TinyBlocks\Mapper\MappingContext;
use TinyBlocks\Mapper\NamingStrategy;

final readonly class Context implements MappingContext
{
    public function __construct(
        private Engine $engine,
        private string $targetType,
        private Configuration $configuration
    ) {
    }

    public static function cast(MappingContext $context): Context
    {
        if ($context instanceof Context) {
            return $context;
        }

        $template = 'Built-in mapping requires the engine-supplied context, got %s.';

        throw new ForeignMappingContext(message: sprintf($template, $context::class));
    }

    public function read(string $type, mixed $source): object
    {
        return $this->engine->read(type: $type, source: $source);
    }

    public function write(mixed $value): mixed
    {
        return $this->engine->write(value: $value, configuration: $this->configuration);
    }

    public function naming(): NamingStrategy
    {
        return $this->engine->strategy();
    }

    public function serialize(object $subject): mixed
    {
        return $this->engine->serialize(subject: $subject, configuration: $this->configuration);
    }

    public function targetType(): string
    {
        return $this->targetType;
    }

    public function factoryRead(string $type, string $method, mixed $source): object
    {
        return $this->engine->factoryRead(type: $type, method: $method, source: $source);
    }

    /**
     * @template T of object
     * @param class-string<T> $type
     * @return T
     */
    public function reflectionRead(string $type, array $source): object
    {
        return $this->engine->reflectionRead(type: $type, source: $source);
    }

    public function reflectionWrite(object $subject): array
    {
        return $this->engine->reflectionWrite(subject: $subject, configuration: $this->configuration);
    }
}
