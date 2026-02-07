<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Collection;

final class ScalarMappingTest extends TestCase
{
    public function testNullIsPreserved(): void
    {
        /** @Given a collection with an explicit null */
        $collection = Collection::createFrom(elements: [null]);

        /** @When converting to array */
        $actual = $collection->toArray();

        /** @Then the null should be preserved */
        self::assertSame([null], $actual);
    }

    public function testMixedScalarsWithNull(): void
    {
        /** @Given a collection with mixed scalar types including null */
        $collection = Collection::createFrom(elements: [
            'string',
            123,
            45.67,
            true,
            false,
            null
        ]);

        /** @When converting to array */
        $actual = $collection->toArray();

        /** @Then all types should be preserved correctly */
        self::assertSame(['string', 123, 45.67, true, false, null], $actual);
    }

    public function testOnlyNullValues(): void
    {
        /** @Given a collection with only null values */
        $collection = Collection::createFrom(elements: [null, null, null]);

        /** @When converting to array */
        $actual = $collection->toArray();

        /** @Then all nulls should be preserved */
        self::assertSame([null, null, null], $actual);
        self::assertCount(3, $actual);
    }

    public function testNullToJson(): void
    {
        /** @Given a collection with null and text values */
        $collection = Collection::createFrom(elements: [null, 'text', null]);

        /** @When converting to JSON */
        $actual = $collection->toJson();

        /** @Then nulls should appear in JSON */
        self::assertJsonStringEqualsJsonString('[null,"text",null]', $actual);
    }

    public function testZeroAndEmptyStringArePersisted(): void
    {
        /** @Given a collection with falsy scalar values */
        $collection = Collection::createFrom(elements: [0, 0.0, '', false, null]);

        /** @When converting to array */
        $actual = $collection->toArray();

        /** @Then each value should remain distinct */
        self::assertSame([0, 0.0, '', false, null], $actual);
    }

    public function testEmptyCollection(): void
    {
        /** @Given an empty collection */
        $collection = Collection::createFrom(elements: []);

        /** @When converting to array */
        $actual = $collection->toArray();

        /** @Then result should be an empty array */
        self::assertSame([], $actual);
        self::assertEmpty($actual);
    }

    public function testEmptyCollectionToJson(): void
    {
        /** @Given an empty collection */
        $collection = Collection::createFrom(elements: []);

        /** @When converting to JSON */
        $actual = $collection->toJson();

        /** @Then JSON should be an empty array */
        self::assertJsonStringEqualsJsonString('[]', $actual);
    }

    public function testEmptyStringsArePreserved(): void
    {
        /** @Given a collection with empty strings */
        $collection = Collection::createFrom(elements: ['', '', '']);

        /** @When converting to array */
        $actual = $collection->toArray();

        /** @Then empty strings should be preserved */
        self::assertSame(['', '', ''], $actual);
        self::assertCount(3, $actual);
    }

    public function testEmptyArraysArePreserved(): void
    {
        /** @Given a collection with empty arrays */
        $collection = Collection::createFrom(elements: [[], [], []]);

        /** @When converting to array */
        $actual = $collection->toArray();

        /** @Then empty arrays should be preserved */
        self::assertSame([[], [], []], $actual);
    }

    public function testMixedEmptyValues(): void
    {
        /** @Given a collection with various empty values */
        $collection = Collection::createFrom(elements: ['', [], 0, false, null]);

        /** @When converting to array */
        $actual = $collection->toArray();

        /** @Then all empty values should be preserved distinctly */
        self::assertSame(['', [], 0, false, null], $actual);
    }

    public function testNestedEmptyStructures(): void
    {
        /** @Given a collection with nested empty structures */
        $collection = Collection::createFrom(elements: [
            ['empty' => []],
            ['nested' => ['deep' => []]],
            []
        ]);

        /** @When converting to array */
        $actual = $collection->toArray();

        /** @Then the nested structure should be preserved */
        self::assertSame([
            ['empty' => []],
            ['nested' => ['deep' => []]],
            []
        ], $actual);
    }

    public function testAllEmptyItemsToJson(): void
    {
        /** @Given a collection where all items are empty */
        $collection = Collection::createFrom(elements: [[], '', null, 0, false]);

        /** @When converting to JSON */
        $actual = $collection->toJson();

        /** @Then JSON should be a valid non-empty string */
        self::assertIsString($actual);
        self::assertNotEmpty($actual);
    }
}
