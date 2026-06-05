<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Unit;

use LogicException;
use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\MemberId;
use Test\TinyBlocks\Mapper\Models\Owner;
use Test\TinyBlocks\Mapper\Models\Pix;
use TinyBlocks\Mapper\Codec;
use TinyBlocks\Mapper\Identity;
use TinyBlocks\Mapper\Mapper;
use TinyBlocks\Mapper\Mapping;
use TinyBlocks\Mapper\MappingContext;
use TinyBlocks\Mapper\NamingStrategy;
use TinyBlocks\Mapper\Subtype;

final class MappingContractTest extends TestCase
{
    public function testReadWhenContextIsNotEngineSuppliedThenLogicExceptionIsRaised(): void
    {
        /** @Given a Subtype mapping returned by the public factory */
        $mapping = Subtype::by(field: 'type', types: [Pix::class]);

        /** @And a foreign MappingContext implementation not produced by the engine */
        $context = new class () implements MappingContext {
            public function read(string $type, mixed $source): object
            {
                throw new LogicException('Unreachable.');
            }

            public function write(mixed $value): mixed
            {
                throw new LogicException('Unreachable.');
            }

            public function naming(): NamingStrategy
            {
                return Identity::create();
            }
        };

        /** @Then a logic exception is raised */
        $this->expectException(LogicException::class);

        /** @When the built-in mapping is read with the foreign context */
        $mapping->read(source: ['type' => 'pix'], context: $context);
    }

    public function testToArrayWhenCustomMappingDelegatesNestedToContextThenNestedIsSerialized(): void
    {
        /** @Given a user-defined Mapping that serializes an Owner by delegating its MemberId to context->write */
        $mapping = new class () implements Mapping {
            public function read(mixed $source, MappingContext $context): object
            {
                return new Owner(name: 'unused', memberId: new MemberId(value: 'unused'));
            }

            public function write(object $subject, MappingContext $context): mixed
            {
                assert($subject instanceof Owner);

                return [
                    'memberId' => $context->write(value: $subject->memberId),
                    'name'     => $subject->name
                ];
            }
        };

        /** @And a mapper registering that custom Mapping for Owner */
        $mapper = Mapper::create()->withMapping(type: Owner::class, mapping: $mapping);

        /** @When an Owner is serialized */
        $array = $mapper->toArray(source: new Owner(name: 'Alice', memberId: new MemberId(value: 'm-1')));

        /** @Then the nested MemberId is unwrapped through context->write */
        self::assertSame(['memberId' => 'm-1', 'name' => 'Alice'], $array);
    }

    public function testToObjectWhenCustomMappingDelegatesNestedToContextThenRegisteredCodecIsHonored(): void
    {
        /** @Given a user-defined Mapping that builds an Owner by delegating its MemberId to context->read */
        $mapping = new class () implements Mapping {
            public function read(mixed $source, MappingContext $context): object
            {
                assert(is_array($source));

                return new Owner(
                    name: $source['name'],
                    memberId: $context->read(type: MemberId::class, source: $source['member'])
                );
            }

            public function write(object $subject, MappingContext $context): mixed
            {
                assert($subject instanceof Owner);

                return ['member' => $subject->memberId->value(), 'name' => $subject->name];
            }
        };

        /** @And a transforming Codec registered for the nested MemberId */
        $codec = Codec::from(
            decode: static fn(string $value): MemberId => new MemberId(value: strtoupper($value)),
            encode: static fn(MemberId $memberId): string => $memberId->value()
        );

        /** @And a mapper registering the Codec for MemberId and the custom Mapping for Owner */
        $mapper = Mapper::create()
            ->withMapping(type: MemberId::class, mapping: $codec)
            ->withMapping(type: Owner::class, mapping: $mapping);

        /** @When an Owner is hydrated from the source */
        $owner = $mapper->toObject(type: Owner::class, source: ['member' => 'm-1', 'name' => 'Alice']);

        /** @Then the nested MemberId is decoded through the registered Codec */
        self::assertSame('M-1', $owner->memberId->value());
    }
}
