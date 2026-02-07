<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Collection;
use Test\TinyBlocks\Mapper\Models\Customer;
use TinyBlocks\Mapper\KeyPreservation;

final class KeyPreservationMappingTest extends TestCase
{
    public function testDiscardNumericKeys(): void
    {
        /** @Given a collection with numeric keys */
        $collection = Collection::createFrom(elements: [
            10 => 'ten',
            20 => 'twenty',
            30 => 'thirty'
        ]);

        /** @When converting with DISCARD */
        $actual = $collection->toArray(keyPreservation: KeyPreservation::DISCARD);

        /** @Then keys should be reindexed from zero */
        self::assertSame(['ten', 'twenty', 'thirty'], $actual);
        self::assertSame([0, 1, 2], array_keys($actual));
    }

    public function testDiscardStringKeys(): void
    {
        /** @Given a collection with string keys */
        $collection = Collection::createFrom(elements: [
            'first'  => 'value1',
            'second' => 'value2',
            'third'  => 'value3'
        ]);

        /** @When converting with DISCARD */
        $actual = $collection->toArray(keyPreservation: KeyPreservation::DISCARD);

        /** @Then string keys should be discarded */
        self::assertSame(['value1', 'value2', 'value3'], $actual);
        self::assertArrayNotHasKey('first', $actual);
        self::assertArrayNotHasKey('second', $actual);
        self::assertArrayNotHasKey('third', $actual);
    }

    public function testPreserveStringKeys(): void
    {
        /** @Given a collection with string keys */
        $collection = Collection::createFrom(elements: [
            'alpha' => 100,
            'beta'  => 200,
            'gamma' => 300
        ]);

        /** @When converting with PRESERVE */
        $actual = $collection->toArray();

        /** @Then string keys should be preserved */
        self::assertSame([
            'alpha' => 100,
            'beta'  => 200,
            'gamma' => 300
        ], $actual);
    }

    public function testPreserveNumericKeys(): void
    {
        /** @Given a collection with non-sequential numeric keys */
        $collection = Collection::createFrom(elements: [
            5  => 'five',
            10 => 'ten',
            15 => 'fifteen'
        ]);

        /** @When converting with PRESERVE */
        $actual = $collection->toArray();

        /** @Then original numeric keys should be preserved */
        self::assertSame([
            5  => 'five',
            10 => 'ten',
            15 => 'fifteen'
        ], $actual);
    }

    public function testDiscardKeysToJson(): void
    {
        /** @Given a collection with string keys */
        $collection = Collection::createFrom(elements: [
            'key1' => 'a',
            'key2' => 'b',
            'key3' => 'c'
        ]);

        /** @When converting to JSON with DISCARD */
        $actual = $collection->toJson(keyPreservation: KeyPreservation::DISCARD);

        /** @Then JSON should be an array without keys */
        self::assertJsonStringEqualsJsonString('["a","b","c"]', $actual);
    }

    public function testPreserveKeysToJson(): void
    {
        /** @Given a collection with string keys */
        $collection = Collection::createFrom(elements: [
            'name' => 'John',
            'age'  => 30,
            'city' => 'NYC'
        ]);

        /** @When converting to JSON with PRESERVE */
        $actual = $collection->toJson();

        /** @Then JSON should be an object with preserved keys */
        self::assertJsonStringEqualsJsonString('{"name":"John","age":30,"city":"NYC"}', $actual);
    }

    public function testDefaultIsPreserve(): void
    {
        /** @Given a collection with keys */
        $collection = Collection::createFrom(elements: ['a' => 1, 'b' => 2]);

        /** @When converting without specifying KeyPreservation */
        $actual = $collection->toArray();

        /** @Then keys should be preserved by default */
        self::assertSame(['a' => 1, 'b' => 2], $actual);
    }

    public function testDiscardKeysOnComplexObject(): void
    {
        /** @Given a Customer with named properties */
        $customer = new Customer(name: 'Alice', score: 100, gender: 'female');

        /** @When converting to array with DISCARD */
        $actual = $customer->toArray(keyPreservation: KeyPreservation::DISCARD);

        /** @Then property names should be discarded and values indexed numerically */
        self::assertSame(['Alice', 100, 'female'], $actual);
        self::assertArrayNotHasKey('name', $actual);
        self::assertArrayNotHasKey('score', $actual);
        self::assertArrayNotHasKey('gender', $actual);
    }
}
