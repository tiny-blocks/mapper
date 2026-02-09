<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Amount;
use Test\TinyBlocks\Mapper\Models\Dragon;
use Test\TinyBlocks\Mapper\Models\DragonSkills;
use Test\TinyBlocks\Mapper\Models\DragonType;
use Test\TinyBlocks\Mapper\Models\Task;
use TinyBlocks\Mapper\Internal\Exceptions\InvalidCast;

final class EnumMappingTest extends TestCase
{
    public function testEnum(): void
    {
        /** @Given a Dragon with a pure enum type */
        $dragon = Dragon::fromIterable(iterable: [
            'name'   => 'Smaug',
            'type'   => 'FIRE',
            'power'  => 9999.99,
            'skills' => []
        ]);

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

    public function testEnumBackedInt(): void
    {
        /** @Given a Task with an array of backed int enums */
        $task = Task::fromIterable(iterable: [
            'title'    => 'Fix bug',
            'priority' => 3
        ]);

        /** @When mapping the Task to an array */
        $actual = $task->toArray();

        /** @Then the mapped array should have expected values */
        $expected = [
            'title'    => 'Fix bug',
            'priority' => 3
        ];

        self::assertSame($expected, $actual);

        /** @And the JSON representation should be the mapped JSON object */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $task->toJson());
    }

    public function testEnumBackedString(): void
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

    public function testEnumWhenInvalidCast(): void
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
