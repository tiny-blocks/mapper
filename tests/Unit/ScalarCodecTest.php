<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Unit;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Reference;
use Test\TinyBlocks\Mapper\Models\Ticket;
use TinyBlocks\Mapper\Codec;
use TinyBlocks\Mapper\Exceptions\UnmappableSource;
use TinyBlocks\Mapper\Mapper;

final class ScalarCodecTest extends TestCase
{
    public function testToObjectWhenStringSourceThenTextScalarCodecIsSelected(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a string source is mapped directly to a type carrying ScalarCodec attributes */
        $reference = $mapper->toObject(type: Reference::class, source: 'alpha');

        /** @Then the decode whose parameter is typed string is selected */
        self::assertSame('text', $reference->origin());
    }

    public function testToArrayWhenScalarCodecSubjectThenFirstPairEncodeIsUsed(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a reference built from text is serialized to an array */
        $array = $mapper->toArray(source: Reference::fromText(value: 'alpha'));

        /** @Then the first declared pair's encode produces the scalar form */
        self::assertSame(['alpha'], $array);
    }

    public function testToObjectWhenNestedNumberSourceThenNumberScalarCodecIsSelected(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a ticket is mapped from a row whose reference column holds an integer */
        $ticket = $mapper->toObject(type: Ticket::class, source: ['reference' => 7]);

        /** @Then the decode whose parameter is typed int builds the nested reference */
        self::assertEquals(new Ticket(reference: Reference::fromNumber(value: 7)), $ticket);
    }

    public function testToObjectAndToArrayWhenScalarCodecRoundTripsThenScalarIsPreserved(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a string source is decoded into a reference and serialized back to an array */
        $array = $mapper->toArray(source: $mapper->toObject(type: Reference::class, source: 'alpha'));

        /** @Then the round-trip preserves the original scalar */
        self::assertSame(['alpha'], $array);
    }

    public function testToObjectWhenNoScalarCodecAcceptsSourceTypeThenUnmappableSourceIsRaised(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @Then an unmappable-source exception is raised */
        $this->expectException(UnmappableSource::class);

        /** @When a ticket is mapped from a row whose reference column holds a float */
        $mapper->toObject(type: Ticket::class, source: ['reference' => 1.5]);
    }

    public function testToArrayWhenRegisteredCodecOverridesScalarCodecThenRegisteredEncodeIsUsed(): void
    {
        /** @Given a mapper with a Codec registered for the ScalarCodec type */
        $mapper = Mapper::create()->withMapping(
            type: Reference::class,
            mapping: Codec::from(
                decode: static fn(string $raw): Reference => Reference::fromText(value: $raw),
                encode: static fn(Reference $reference): string => 'registered'
            )
        );

        /** @When a reference is serialized to an array */
        $array = $mapper->toArray(source: Reference::fromText(value: 'alpha'));

        /** @Then the registered encode takes precedence over the attribute */
        self::assertSame(['registered'], $array);
    }

    public function testToObjectWhenRegisteredCodecOverridesScalarCodecThenRegisteredDecodeIsUsed(): void
    {
        /** @Given a mapper with a Codec registered for the ScalarCodec type */
        $mapper = Mapper::create()->withMapping(
            type: Reference::class,
            mapping: Codec::from(
                decode: static fn(string $raw): Reference => Reference::fromNumber(value: strlen($raw)),
                encode: static fn(Reference $reference): string => $reference->toText()
            )
        );

        /** @When a string source is mapped directly to the type */
        $reference = $mapper->toObject(type: Reference::class, source: 'alpha');

        /** @Then the registered decode takes precedence over the attribute */
        self::assertEquals(Reference::fromNumber(value: 5), $reference);
    }

    public function testToObjectWhenScalarCodecReceivesNonScalarSourceThenUnmappableSourceIsRaised(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @Then an unmappable-source exception is raised */
        $this->expectException(UnmappableSource::class);

        /** @When a non-scalar source is mapped directly to a ScalarCodec type */
        $mapper->toObject(type: Reference::class, source: ['unexpected' => 'value']);
    }
}
