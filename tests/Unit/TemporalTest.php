<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Unit;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Booking;
use TinyBlocks\Mapper\Codec;
use TinyBlocks\Mapper\Mapper;
use TinyBlocks\Time\Instant;
use TinyBlocks\Time\LocalDate;

final class TemporalTest extends TestCase
{
    public function testToArrayWhenLocalDateHasScalarCodecThenDateIsNotWidenedToDatetime(): void
    {
        /** @Given a mapper with a scalar codec registered for LocalDate */
        $mapper = Mapper::create()->withMapping(
            type: LocalDate::class,
            mapping: Codec::from(
                decode: static fn(string $iso): LocalDate => LocalDate::fromString(value: $iso),
                encode: static fn(LocalDate $date): string => $date->toIso8601()
            )
        );

        /** @When a booking carrying a date-only LocalDate is serialized */
        $array = $mapper->toArray(source: new Booking(
            stayDate: LocalDate::fromString(value: '2026-05-23'),
            confirmedAt: Instant::fromString(value: '2026-02-17T16:30:00+00:00')
        ));

        /** @Then the LocalDate is emitted as its canonical date-only string, not a widened datetime */
        self::assertSame('2026-05-23', $array['stayDate']);
    }

    public function testToObjectWhenLocalDateHasScalarCodecThenScalarIsDecodedToValueObject(): void
    {
        /** @Given a mapper with a scalar codec registered for LocalDate */
        $mapper = Mapper::create()->withMapping(
            type: LocalDate::class,
            mapping: Codec::from(
                decode: static fn(string $iso): LocalDate => LocalDate::fromString(value: $iso),
                encode: static fn(LocalDate $date): string => $date->toIso8601()
            )
        );

        /** @When a bare date-only scalar is mapped directly to a LocalDate */
        $date = $mapper->toObject(type: LocalDate::class, source: '2026-05-23');

        /** @Then the codec decodes the scalar into the canonical value object */
        self::assertSame('2026-05-23', $date->toIso8601());
    }

    public function testToObjectAndToArrayWhenInstantPropertyThenUnwrapRoundTripsToCanonicalIso(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @And a booking whose confirmation instant is fixed to a seconds-precision moment */
        $original = new Booking(
            stayDate: LocalDate::fromString(value: '2026-05-23'),
            confirmedAt: Instant::fromString(value: '2026-02-17T16:30:00+00:00')
        );

        /** @When the booking is serialized and rebuilt without any registered codec */
        $rebuilt = $mapper->toObject(type: Booking::class, source: $mapper->toArray(source: $original));

        /** @Then the Instant round-trips to its canonical ISO-8601 string through the unwrap chain */
        self::assertSame('2026-02-17T16:30:00+00:00', $rebuilt->confirmedAt->toIso8601());
    }

    public function testToObjectAndToArrayWhenLocalDateHasScalarCodecThenRoundTripPreservesCanonicalForms(): void
    {
        /** @Given a mapper with a scalar codec registered for LocalDate */
        $mapper = Mapper::create()->withMapping(
            type: LocalDate::class,
            mapping: Codec::from(
                decode: static fn(string $iso): LocalDate => LocalDate::fromString(value: $iso),
                encode: static fn(LocalDate $date): string => $date->toIso8601()
            )
        );

        /** @And a booking carrying a date-only LocalDate and a seconds-precision Instant */
        $original = new Booking(
            stayDate: LocalDate::fromString(value: '2026-05-23'),
            confirmedAt: Instant::fromString(value: '2026-02-17T16:30:00+00:00')
        );

        /** @When the booking is serialized and rebuilt */
        $rebuilt = $mapper->toObject(type: Booking::class, source: $mapper->toArray(source: $original));

        /** @Then both temporal values round-trip to their canonical strings */
        self::assertSame(
            ['2026-05-23', '2026-02-17T16:30:00+00:00'],
            [$rebuilt->stayDate->toIso8601(), $rebuilt->confirmedAt->toIso8601()]
        );
    }
}
