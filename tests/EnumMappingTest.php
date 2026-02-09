<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Amount;
use Test\TinyBlocks\Mapper\Models\Currency;
use Test\TinyBlocks\Mapper\Models\Dragon;
use Test\TinyBlocks\Mapper\Models\DragonSkills;
use Test\TinyBlocks\Mapper\Models\DragonType;
use TinyBlocks\Mapper\Internal\Exceptions\InvalidCast;

final class EnumMappingTest extends TestCase
{
    public function testPureEnum(): void
    {
        /** @Given a Dragon with a pure enum type */
        $dragon = new Dragon(
            name: 'Smaug',
            type: DragonType::FIRE,
            power: 9999.99,
            skills: []
        );

        /** @When mapping the Dragon to an array */
        $actual = $dragon->toArray();

        /** @Then the mapped array should have expected values */
        $expected = [
            'name'   => 'Smaug',
            'type'   => 'FIRE',
            'power'  => 9999.99,
            'skills' => []
        ];

        self::assertSame($expected, $actual);

        /** @And the JSON representation should be the mapped JSON object */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $dragon->toJson());
    }

    public function testBackedStringEnum(): void
    {
        /** @Given an Amount with a backed string enum */
        $amount = Amount::from(value: 100.00, currency: Currency::BRL);

        /** @When mapping the Amount to an array */
        $actual = $amount->toArray();

        /** @Then the mapped array should have expected values */
        $expected = [
            'value'    => 100.00,
            'currency' => 'BRL'
        ];

        self::assertSame($expected, $actual);

        /** @And the JSON representation should be the mapped JSON object */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $amount->toJson());
    }

    public function testBackedStringEnumArray(): void
    {
        /** @Given a Dragon with an array of backed string enums */
        $dragon = new Dragon(
            name: 'Alduin',
            type: DragonType::FIRE,
            power: 10000.00,
            skills: [
                DragonSkills::FLY,
                DragonSkills::ELEMENTAL_BREATH,
                DragonSkills::REGENERATION
            ]
        );

        /** @When mapping the Dragon to an array */
        $actual = $dragon->toArray();

        /** @Then the mapped array should have expected values */
        $expected = [
            'name'   => 'Alduin',
            'type'   => 'FIRE',
            'power'  => 10000.00,
            'skills' => ['fly', 'elemental_breath', 'regeneration']
        ];

        self::assertSame($expected, $actual);

        /** @And the JSON representation should be the mapped JSON object */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $dragon->toJson());
    }

    public function testBackedEnumFromIterable(): void
    {
        /** @Given an Amount created from iterable data with a backed enum value */
        $amount = Amount::fromIterable(iterable: [
            'value'    => 500.00,
            'currency' => 'USD'
        ]);

        /** @When mapping the Amount to an array */
        $actual = $amount->toArray();

        /** @Then the mapped array should have expected values */
        $expected = [
            'value'    => 500.00,
            'currency' => 'USD'
        ];

        self::assertSame($expected, $actual);

        /** @And the JSON representation should be the mapped JSON object */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $amount->toJson());
    }

    public function testEnumArrayFromIterable(): void
    {
        /** @Given a Dragon created from iterable data with enum values as strings */
        $dragon = Dragon::fromIterable(iterable: [
            'name'   => 'Bahamut',
            'type'   => 'FIRE',
            'power'  => 15000.00,
            'skills' => ['fly', 'spell', 'elemental_breath']
        ]);

        /** @When mapping the Dragon to an array */
        $actual = $dragon->toArray();

        /** @Then the mapped array should have expected values */
        $expected = [
            'name'   => 'Bahamut',
            'type'   => 'FIRE',
            'power'  => 15000.00,
            'skills' => ['fly', 'spell', 'elemental_breath']
        ];

        self::assertSame($expected, $actual);

        /** @And the JSON representation should be the mapped JSON object */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $dragon->toJson());
    }

    public function testInvalidEnumValueThrowsException(): void
    {
        /** @Given data with an invalid currency value */
        $data = [
            'value'    => 250.00,
            'currency' => 'INVALID'
        ];

        /** @Then an exception should be thrown */
        $this->expectException(InvalidCast::class);
        $this->expectExceptionMessage('Invalid value <INVALID> for enum <Test\TinyBlocks\Mapper\Models\Currency>.');

        /** @When creating from iterable */
        Amount::fromIterable(iterable: $data);
    }
}
